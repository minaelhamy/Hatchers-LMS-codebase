<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Hustler extends MY_Controller
{
    public $load;
    public $input;
    public $db;
    public $session;
    public $output;
    public $site_m;
    public $hustler_investor_m;
    public $hustler_profile_m;
    public $hustler_conversation_m;
    public $hustler_action_item_m;
    public $hustler_market_asset_m;
    public $hatcher_ai_settings_m;

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper(['url', 'form']);
        $this->load->model('site_m');
        $this->load->model('hustler_investor_m');
        $this->load->model('hustler_profile_m');
        $this->load->model('hustler_conversation_m');
        $this->load->model('hustler_action_item_m');
        $this->load->model('hustler_market_asset_m');
        $this->load->model('hatcher_ai_settings_m');

        $this->data['siteinfos'] = $this->site_m->get_site();
    }

    public function index()
    {
        if ($this->_isLoggedIn()) {
            redirect(base_url('hustler/dashboard'));
        }

        $this->_render('hustler/login', [
            'page_title' => 'Hustler Login',
            'form_error' => ''
        ]);
    }

    public function login()
    {
        if ($this->input->method(true) !== 'POST') {
            redirect(base_url('hustler'));
        }

        if (!$this->_tablesInstalled()) {
            $this->_render('hustler/login', [
                'page_title' => 'Hustler Login',
                'form_error' => 'Hustler tables are not installed yet. Run the SQL update first.'
            ]);
            return;
        }

        $username = trim((string) $this->input->post('username'));
        $password = (string) $this->input->post('password');

        if ($username === '' || $password === '') {
            $this->_render('hustler/login', [
                'page_title' => 'Hustler Login',
                'form_error' => 'Username and password are required.'
            ]);
            return;
        }

        $investor = $this->hustler_investor_m->get_single_hustler_investor([
            'username' => $username,
            'is_active' => 1
        ]);

        if (!customCompute($investor) || (string) $investor->password !== $this->_hash($password)) {
            $this->_render('hustler/login', [
                'page_title' => 'Hustler Login',
                'form_error' => 'Invalid login credentials.'
            ]);
            return;
        }

        $this->session->set_userdata([
            'hustler_logged_in' => true,
            'hustler_investor_id' => $investor->hustler_investor_id,
            'hustler_investor_name' => $investor->name,
            'hustler_investor_email' => $investor->email
        ]);

        $this->hustler_investor_m->update_hustler_investor([
            'last_login_at' => date('Y-m-d H:i:s')
        ], $investor->hustler_investor_id);

        $this->_ensureProfile($investor->hustler_investor_id);
        redirect(base_url('hustler/dashboard'));
    }

    public function dashboard()
    {
        $investor = $this->_requireAuth();
        $profile = $this->_ensureProfile($investor->hustler_investor_id);
        $conversations = $this->hustler_conversation_m->get_for_profile($profile->hustler_founder_profile_id, 18);
        $conversations = array_reverse($conversations);
        $marketGate = $this->_marketAccessGate($profile);

        $this->_render('hustler/dashboard', [
            'page_title' => 'Hustler Workspace',
            'investor' => $investor,
            'profile' => $profile,
            'action_items' => $this->hustler_action_item_m->get_for_profile($profile->hustler_founder_profile_id),
            'market_asset' => $this->hustler_market_asset_m->get_latest_for_profile($profile->hustler_founder_profile_id),
            'chat_rows' => $conversations,
            'diagnosis' => $this->_decodeJsonField($profile->last_diagnosis_json),
            'plan' => $this->_decodeJsonField($profile->last_plan_json),
            'market_access_allowed' => $marketGate['allowed'],
            'market_access_reason' => $marketGate['reason']
        ]);
    }

    public function market_access()
    {
        $investor = $this->_requireAuth();
        $profile = $this->_ensureProfile($investor->hustler_investor_id);
        $marketGate = $this->_marketAccessGate($profile);
        if (!$marketGate['allowed']) {
            $this->session->set_flashdata('hustler_market_gate_error', $marketGate['reason']);
            redirect(base_url('hustler/dashboard'));
            return;
        }

        $this->_render('hustler/market_access', [
            'page_title' => 'Market Access',
            'investor' => $investor,
            'profile' => $profile,
            'action_items' => $this->hustler_action_item_m->get_for_profile($profile->hustler_founder_profile_id),
            'market_asset' => $this->hustler_market_asset_m->get_latest_for_profile($profile->hustler_founder_profile_id)
        ]);
    }

    public function chat()
    {
        $investor = $this->_requireAuth(true);

        if (!$this->_tablesInstalled()) {
            $this->_json(['ok' => false, 'error' => 'Hustler tables are not installed yet.']);
            return;
        }

        $message = trim((string) $this->input->post('message'));
        if ($message === '') {
            $this->_json(['ok' => false, 'error' => 'Message is required.']);
            return;
        }

        $profile = $this->_ensureProfile($investor->hustler_investor_id);

        $this->hustler_conversation_m->insert_hustler_conversation([
            'hustler_founder_profile_id' => $profile->hustler_founder_profile_id,
            'role' => 'user',
            'message' => $message,
            'message_kind' => 'chat',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $apiKey = $this->_getOpenAIKey();
        if ($apiKey === '') {
            $this->_json(['ok' => false, 'error' => 'OpenAI API key is not configured.']);
            return;
        }

        $settings = $this->hatcher_ai_settings_m->get_latest_settings();
        $history = $this->hustler_conversation_m->get_for_profile($profile->hustler_founder_profile_id, 24);
        $history = array_reverse($history);

        $userMessageCount = $this->_countUserMessages($profile->hustler_founder_profile_id);
        $discoveryMode = $this->_shouldUseDiscoveryMode($profile, $userMessageCount);

        $payload = [
            'model' => customCompute($settings) ? $settings->model : 'gpt-4o-mini',
            'input' => $this->_mapConversationForOpenAI($history),
            'instructions' => $this->_buildDiagnosticInstructions($profile, $discoveryMode, $userMessageCount),
            'max_output_tokens' => customCompute($settings) ? (int) $settings->max_tokens : 1200,
            'temperature' => customCompute($settings) ? (float) $settings->temperature : 0.4
        ];

        $response = $this->_callOpenAI($apiKey, $payload);
        if (!$response['ok']) {
            $this->_json(['ok' => false, 'error' => $this->_friendlyOpenAIError($response['error'])]);
            return;
        }

        $responseText = $this->_extractResponseText($response['data']);
        $structured = $this->_decodeStructuredResponse($responseText);

        $assistantReply = isset($structured['assistant_reply']) ? trim((string) $structured['assistant_reply']) : '';
        if ($assistantReply === '') {
            $assistantReply = $this->_buildReadableReplyFromStructured($structured, trim((string) $responseText), $discoveryMode);
        }
        if ($assistantReply === '') {
            $assistantReply = 'I need a little more detail to give you a precise plan. Tell me more about the founder, idea, traction, and constraints.';
        }

        $this->hustler_conversation_m->insert_hustler_conversation([
            'hustler_founder_profile_id' => $profile->hustler_founder_profile_id,
            'role' => 'assistant',
            'message' => $assistantReply,
            'message_kind' => 'diagnostic',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $readyForDiagnosis = isset($structured['ready_for_diagnosis']) ? (bool) $structured['ready_for_diagnosis'] : !$discoveryMode;
        $profile = $this->_syncProfileFromStructuredOutput($profile, $structured, $readyForDiagnosis);
        if ($readyForDiagnosis) {
            $items = $this->_syncActionItems($profile->hustler_founder_profile_id, $structured);
        } else {
            $items = $this->_getCurrentActionItems($profile->hustler_founder_profile_id);
        }
        $marketGate = $this->_marketAccessGate($profile);
        if ($marketGate['allowed']) {
            $assistantReply .= "\n\nYou now have enough context to proceed. Click Market Access in the left menu to review generated Instagram and funnel assets.";
        }

        $this->_json([
            'ok' => true,
            'reply' => $assistantReply,
            'action_items' => $items,
            'diagnosis' => $this->_decodeJsonField($profile->last_diagnosis_json),
            'profile' => $this->_profileViewData($profile),
            'ready_for_diagnosis' => $readyForDiagnosis,
            'market_access_allowed' => $marketGate['allowed'],
            'market_access_reason' => $marketGate['reason']
        ]);
    }

    public function generate_market_access()
    {
        try {
            $investor = $this->_requireAuth(true);

            if (!$this->_tablesInstalled()) {
                $this->_json(['ok' => false, 'error' => 'Hustler tables are not installed yet.']);
                return;
            }

            $profile = $this->_ensureProfile($investor->hustler_investor_id);
            $marketGate = $this->_marketAccessGate($profile);
            if (!$marketGate['allowed']) {
                $this->_json(['ok' => false, 'error' => $marketGate['reason']]);
                return;
            }
            $apiKey = $this->_getOpenAIKey();
            if ($apiKey === '') {
                $this->_json(['ok' => false, 'error' => 'OpenAI API key is not configured.']);
                return;
            }

            $focus = trim((string) $this->input->post('focus'));
            $settings = $this->hatcher_ai_settings_m->get_latest_settings();
            $postCount = $this->_extractRequestedPostCount($focus, 6);

            $payload = [
                'model' => customCompute($settings) ? $settings->model : 'gpt-4o-mini',
                'input' => [
                    [
                        'role' => 'user',
                        'content' => $this->_buildMarketAccessPrompt($profile, $focus, $postCount)
                    ]
                ],
                'instructions' => 'You create market-entry research packs for founders. Use founder context and inferred competitor intelligence. Return JSON only.',
                'max_output_tokens' => 2200,
                'temperature' => 0.5
            ];

            $response = $this->_callOpenAI($apiKey, $payload);
            if (!$response['ok']) {
                $this->_json(['ok' => false, 'error' => $this->_friendlyOpenAIError($response['error'])]);
                return;
            }

            $responseText = $this->_extractResponseText($response['data']);
            $structured = $this->_decodeStructuredResponse($responseText);
            if (!is_array($structured) || empty($structured)) {
                $structured = $this->_repairMarketAccessJson($apiKey, $responseText, customCompute($settings) ? $settings->model : 'gpt-4o-mini');
            }
            if (!is_array($structured) || empty($structured)) {
                $this->_json(['ok' => false, 'error' => 'Could not generate market access assets from AI output.']);
                return;
            }

            $socialPosts = $this->_extractSocialPosts($structured, $postCount);
            if (!customCompute($socialPosts)) {
                $socialPosts = $this->_fallbackSocialPosts($profile, $postCount);
            }
            $postImages = $this->_generateSocialPostImages($apiKey, $socialPosts, $profile, 6);
            $instagramProfile = $this->_extractInstagramProfile($structured, $profile, $socialPosts);
            $funnelSuggestions = $this->_extractFunnels($structured, $profile);

            $marketOverview = $this->_textFieldOrFallback($structured, 'market_overview');
            if ($marketOverview === '') {
                $marketOverview = 'Market overview pending. Add more founder context in Weekly Plan chat to generate a sharper market read.';
            }

            $icp = $this->_textFieldOrFallback($structured, 'ideal_customer_profile');
            if ($icp === '') {
                $icp = 'Ideal customer profile pending. Share clearer ICP clues (buyer type, geography, channel) and regenerate.';
            }

            $assetData = [
                'market_overview' => $marketOverview,
                'ideal_customer_profile' => $icp,
                'instagram_profile_json' => json_encode($instagramProfile),
                'competitor_patterns_json' => json_encode($this->_normalizeList(isset($structured['competitor_patterns']) ? $structured['competitor_patterns'] : [])),
                'distribution_angles_json' => json_encode($this->_normalizeList(isset($structured['distribution_angles']) ? $structured['distribution_angles'] : [])),
                'social_posts_json' => json_encode(array_map(function ($row) {
                    return isset($row['caption']) ? $row['caption'] : '';
                }, $socialPosts)),
                'post_images_json' => json_encode($postImages),
                'funnel_suggestions_json' => json_encode($funnelSuggestions),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->hustler_market_asset_m->upsert_for_profile($profile->hustler_founder_profile_id, $assetData);

            $this->_json([
                'ok' => true,
                'market_asset' => [
                    'market_overview' => $assetData['market_overview'],
                    'ideal_customer_profile' => $assetData['ideal_customer_profile'],
                    'instagram_profile' => json_decode($assetData['instagram_profile_json'], true),
                    'competitor_patterns' => json_decode($assetData['competitor_patterns_json'], true),
                    'distribution_angles' => json_decode($assetData['distribution_angles_json'], true),
                    'social_posts' => json_decode($assetData['social_posts_json'], true),
                    'post_images' => json_decode($assetData['post_images_json'], true),
                    'funnel_suggestions' => json_decode($assetData['funnel_suggestions_json'], true),
                    'updated_at' => $assetData['updated_at']
                ]
            ]);
        } catch (\Throwable $e) {
            $this->_json([
                'ok' => false,
                'error' => 'Market access generation failed. ' . $e->getMessage()
            ]);
        }
    }

    public function logout()
    {
        $this->session->unset_userdata([
            'hustler_logged_in',
            'hustler_investor_id',
            'hustler_investor_name',
            'hustler_investor_email'
        ]);

        redirect(base_url('hustler'));
    }

    public function restart_profile()
    {
        $investor = $this->_requireAuth();
        $profile = $this->_ensureProfile($investor->hustler_investor_id);
        $profileID = (int) $profile->hustler_founder_profile_id;

        $this->db->delete('hustler_conversations', ['hustler_founder_profile_id' => $profileID]);
        $this->db->delete('hustler_action_items', ['hustler_founder_profile_id' => $profileID]);
        $this->db->delete('hustler_market_assets', ['hustler_founder_profile_id' => $profileID]);

        $this->hustler_profile_m->upsert_profile($investor->hustler_investor_id, [
            'founder_name' => '',
            'founder_email' => '',
            'profile_photo_url' => '',
            'company_logo_url' => '',
            'company_name' => '',
            'idea_summary' => '',
            'target_customer_profile' => '',
            'stage_label' => 'Needs diagnosis',
            'skills_summary' => '',
            'weekly_time_commitment' => '',
            'capital_available' => '',
            'traction_summary' => '',
            'constraints_summary' => '',
            'competitor_notes' => '',
            'memory_summary' => '',
            'last_diagnosis_json' => json_encode([]),
            'last_plan_json' => json_encode([]),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $this->session->set_flashdata('hustler_market_gate_error', 'Profile restarted. You can start from scratch with a new idea.');
        redirect(base_url('hustler/dashboard'));
    }

    public function upload_media()
    {
        $investor = $this->_requireAuth();
        if ($this->input->method(true) !== 'POST') {
            redirect(base_url('hustler/dashboard'));
            return;
        }

        $profile = $this->_ensureProfile($investor->hustler_investor_id);
        $mediaType = trim((string) $this->input->post('media_type'));
        if (!isset($_FILES['media_file']) || !is_array($_FILES['media_file'])) {
            $this->session->set_flashdata('hustler_market_gate_error', 'No file selected.');
            redirect(base_url('hustler/dashboard'));
            return;
        }

        $filePath = $this->_storeProfileMediaFile($_FILES['media_file'], $profile->hustler_founder_profile_id, $mediaType);
        if ($filePath === '') {
            $this->session->set_flashdata('hustler_market_gate_error', 'Upload failed. Please use JPG, PNG, or WEBP under 5MB.');
            redirect(base_url('hustler/dashboard'));
            return;
        }

        $field = $mediaType === 'company_logo' ? 'company_logo_url' : 'profile_photo_url';
        $this->hustler_profile_m->upsert_profile($investor->hustler_investor_id, [
            $field => $filePath,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $this->session->set_flashdata('hustler_market_gate_error', ucfirst(str_replace('_', ' ', $mediaType)) . ' uploaded.');
        redirect(base_url('hustler/dashboard'));
    }

    private function _render($subview, $data = [])
    {
        $viewData = array_merge($this->data, $data);
        $viewData['subview'] = $subview;
        $this->load->view('hustler/layout', $viewData);
    }

    private function _isLoggedIn()
    {
        return (bool) $this->session->userdata('hustler_logged_in');
    }

    private function _requireAuth($json = false)
    {
        if (!$this->_isLoggedIn()) {
            if ($json) {
                $this->_json(['ok' => false, 'error' => 'Please sign in again.']);
                exit;
            }

            redirect(base_url('hustler'));
        }

        $investorID = (int) $this->session->userdata('hustler_investor_id');
        $investor = $this->hustler_investor_m->get_single_hustler_investor([
            'hustler_investor_id' => $investorID,
            'is_active' => 1
        ]);

        if (!customCompute($investor)) {
            $this->session->unset_userdata([
                'hustler_logged_in',
                'hustler_investor_id',
                'hustler_investor_name',
                'hustler_investor_email'
            ]);

            if ($json) {
                $this->_json(['ok' => false, 'error' => 'Account no longer available.']);
                exit;
            }

            redirect(base_url('hustler'));
        }

        return $investor;
    }

    private function _tablesInstalled()
    {
        $required = [
            'hustler_investors',
            'hustler_founder_profiles',
            'hustler_conversations',
            'hustler_action_items',
            'hustler_market_assets'
        ];

        foreach ($required as $table) {
            if (!$this->db->table_exists($table)) {
                return false;
            }
        }

        return true;
    }

    private function _ensureProfile($investorID)
    {
        $profile = $this->hustler_profile_m->get_by_investor($investorID);
        if (customCompute($profile)) {
            return $profile;
        }

        $this->hustler_profile_m->upsert_profile($investorID, [
            'founder_name' => '',
            'founder_email' => '',
            'profile_photo_url' => '',
            'company_logo_url' => '',
            'company_name' => '',
            'idea_summary' => '',
            'target_customer_profile' => '',
            'stage_label' => 'Needs diagnosis',
            'skills_summary' => '',
            'weekly_time_commitment' => '',
            'capital_available' => '',
            'traction_summary' => '',
            'constraints_summary' => '',
            'competitor_notes' => '',
            'memory_summary' => '',
            'last_diagnosis_json' => json_encode([]),
            'last_plan_json' => json_encode([]),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return $this->hustler_profile_m->get_by_investor($investorID);
    }

    private function _buildDiagnosticInstructions($profile, $discoveryMode = false, $userMessageCount = 0)
    {
        $context = [
            'founder_name' => (string) $profile->founder_name,
            'founder_email' => (string) $profile->founder_email,
            'profile_photo_url' => isset($profile->profile_photo_url) ? (string) $profile->profile_photo_url : '',
            'company_logo_url' => isset($profile->company_logo_url) ? (string) $profile->company_logo_url : '',
            'company_name' => (string) $profile->company_name,
            'idea_summary' => (string) $profile->idea_summary,
            'target_customer_profile' => isset($profile->target_customer_profile) ? (string) $profile->target_customer_profile : '',
            'stage_label' => (string) $profile->stage_label,
            'skills_summary' => (string) $profile->skills_summary,
            'weekly_time_commitment' => (string) $profile->weekly_time_commitment,
            'capital_available' => (string) $profile->capital_available,
            'traction_summary' => (string) $profile->traction_summary,
            'constraints_summary' => (string) $profile->constraints_summary,
            'competitor_notes' => (string) $profile->competitor_notes,
            'memory_summary' => (string) $profile->memory_summary
        ];

        if ($discoveryMode) {
            return "You are Hatchers Hustler, an engaged startup mentor."
                . "\nYou are in discovery mode. Do NOT generate final diagnosis, weekly plans, or milestone/task outputs yet."
                . "\nGoal: ask sharp follow-up questions, one or two at a time, to understand founder context deeply."
                . "\nBe conversational and natural, like a strong ChatGPT-style coach."
                . "\nCollect the following as top priority: founder_name, company_name, company_logo_url (or ask them to upload), idea_summary, target_customer_profile."
                . "\nIf these core fields are clear, set ready_for_diagnosis=true and switch to full diagnosis + weekly plan immediately."
                . "\nWhen you set ready_for_diagnosis=true, explicitly tell the founder they can click Market Access."
                . "\nThen continue with ICP depth, traction, available time, available capital, distribution access, and biggest constraints."
                . "\nReturn JSON only with keys:"
                . "\nassistant_reply (string),"
                . "\nready_for_diagnosis (boolean, keep false unless enough context for a solid diagnosis),"
                . "\nfounder_profile (object with founder_name, founder_email, profile_photo_url, company_logo_url, company_name, idea_summary, target_customer_profile, stage_label, skills_summary, weekly_time_commitment, capital_available, traction_summary, constraints_summary, competitor_notes),"
                . "\nmemory_summary (string)."
                . "\nCurrent user message count: " . (int) $userMessageCount
                . "\nExisting founder context:\n" . json_encode($context);
        }

        return "You are Hatchers Hustler, a diagnosis-first execution routing engine for founders."
            . "\nYou are not a generic chatbot."
            . "\nYour job is to intake founder context, classify the founder stage, diagnose the business, identify the sharpest bottleneck, and output the next best weekly plan."
            . "\nKeep the tone conversational and engaging while still producing structured output."
            . "\nAlways reason through: founder-idea fit first, then DFV (desirability, feasibility, viability), then gaps, then priority actions."
            . "\nIf the founder cannot directly access the first 5-10 customers, cannot book 10 discovery calls next week, depends on enterprise procurement, big-box retail, heavy capital inventory, a large dev team before validation, or a marketplace without supply control, call that out explicitly."
            . "\nAlways factor in time, capital, skills, network access, and founder constraints."
            . "\nWhen information is missing, ask for the most leverage-driving missing detail while still giving a provisional plan."
            . "\nReturn JSON only with keys:"
            . "\nassistant_reply (string for the user-facing message),"
            . "\nready_for_diagnosis (boolean, true),"
            . "\nfounder_profile (object with founder_name, founder_email, profile_photo_url, company_logo_url, company_name, idea_summary, target_customer_profile, stage_label, skills_summary, weekly_time_commitment, capital_available, traction_summary, constraints_summary, competitor_notes),"
            . "\ndiagnosis (object with current_status, founder_idea_fit, dfv_assessment, bottleneck_identification),"
            . "\ngaps (array of short strings covering access, clarity, distribution, unit economics, execution as applicable),"
            . "\npriority_actions (array with 1 to 3 concise action steps),"
            . "\nsuggested_tools (array of concise tool or automation suggestions),"
            . "\nescalation (object with needs_human boolean and reason string),"
            . "\nmemory_summary (string),"
            . "\nweekly_plan (object with headline string, tasks array, milestones array)."
            . "\nEach task must be an object with title and description."
            . "\nEach milestone must be an object with title and description."
            . "\nExisting founder context:\n" . json_encode($context);
    }

    private function _buildMarketAccessPrompt($profile, $focus, $postCount = 6)
    {
        $context = [
            'founder_name' => (string) $profile->founder_name,
            'company_name' => (string) $profile->company_name,
            'company_logo_url' => isset($profile->company_logo_url) ? (string) $profile->company_logo_url : '',
            'idea_summary' => (string) $profile->idea_summary,
            'target_customer_profile' => isset($profile->target_customer_profile) ? (string) $profile->target_customer_profile : '',
            'stage_label' => (string) $profile->stage_label,
            'traction_summary' => (string) $profile->traction_summary,
            'constraints_summary' => (string) $profile->constraints_summary,
            'competitor_notes' => (string) $profile->competitor_notes,
            'memory_summary' => (string) $profile->memory_summary
        ];

        return "Build a market-access brief and social media starter pack for this founder."
            . "\nUse the founder context below and infer likely competitor patterns from broadly known market behavior and channel norms."
            . "\nIf exact competitor names are unknown, explicitly mark competitor insights as inferred."
            . "\nReturn JSON only with keys: market_overview, ideal_customer_profile, instagram_profile, competitor_patterns, distribution_angles, social_posts, funnel_suggestions."
            . "\ninstagram_profile object keys: username, display_name, bio, website, followers, following, logo_url."
            . "\ncompetitor_patterns should be an array of short bullets."
            . "\ndistribution_angles should be an array of short bullets."
            . "\nsocial_posts must contain exactly " . (int) $postCount . " items."
            . "\nEach item can be either a string caption OR an object with keys caption and image_prompt."
            . "\nfunnel_suggestions must contain exactly 3 objects with keys: design_type, title, subtitle, steps (array of 4), cta."
            . "\nAllowed design_type values: awareness, conversion, lead_capture, dark_flow."
            . "\nUse Sell Like Crazy style hooks: bold promise, lead magnet, authority proof, urgency and clear CTA."
            . "\nIf the user asks for Instagram, tailor formats to Instagram (hooks, carousel ideas, reel prompts, captions, CTA)."
            . ($focus !== '' ? "\nFocus area: " . $focus : '')
            . "\nFounder context:\n" . json_encode($context);
    }

    private function _mapConversationForOpenAI($rows)
    {
        $messages = [];
        if (customCompute($rows)) {
            foreach ($rows as $row) {
                $messages[] = [
                    'role' => $row->role,
                    'content' => $row->message
                ];
            }
        }

        return $messages;
    }

    private function _syncProfileFromStructuredOutput($profile, $structured, $readyForDiagnosis = true)
    {
        if (!is_array($structured) || empty($structured)) {
            return $profile;
        }

        $founderProfile = isset($structured['founder_profile']) && is_array($structured['founder_profile'])
            ? $structured['founder_profile']
            : [];

        $diagnosis = isset($structured['diagnosis']) && is_array($structured['diagnosis'])
            ? $structured['diagnosis']
            : [];

        $plan = isset($structured['weekly_plan']) && is_array($structured['weekly_plan'])
            ? $structured['weekly_plan']
            : [];

        $update = [
            'founder_name' => $this->_stringFromArray($founderProfile, 'founder_name', $profile->founder_name),
            'founder_email' => $this->_stringFromArray($founderProfile, 'founder_email', $profile->founder_email),
            'profile_photo_url' => $this->_stringFromArray($founderProfile, 'profile_photo_url', isset($profile->profile_photo_url) ? $profile->profile_photo_url : ''),
            'company_logo_url' => $this->_stringFromArray($founderProfile, 'company_logo_url', isset($profile->company_logo_url) ? $profile->company_logo_url : ''),
            'company_name' => $this->_stringFromArray($founderProfile, 'company_name', $profile->company_name),
            'idea_summary' => $this->_stringFromArray($founderProfile, 'idea_summary', $profile->idea_summary),
            'target_customer_profile' => $this->_extractTargetCustomerProfile($founderProfile, $structured, isset($profile->target_customer_profile) ? $profile->target_customer_profile : ''),
            'stage_label' => $this->_stringFromArray($founderProfile, 'stage_label', $profile->stage_label),
            'skills_summary' => $this->_stringFromArray($founderProfile, 'skills_summary', $profile->skills_summary),
            'weekly_time_commitment' => $this->_stringFromArray($founderProfile, 'weekly_time_commitment', $profile->weekly_time_commitment),
            'capital_available' => $this->_stringFromArray($founderProfile, 'capital_available', $profile->capital_available),
            'traction_summary' => $this->_stringFromArray($founderProfile, 'traction_summary', $profile->traction_summary),
            'constraints_summary' => $this->_stringFromArray($founderProfile, 'constraints_summary', $profile->constraints_summary),
            'competitor_notes' => $this->_stringFromArray($founderProfile, 'competitor_notes', $profile->competitor_notes),
            'memory_summary' => $this->_stringOrDefault(isset($structured['memory_summary']) ? $structured['memory_summary'] : '', $profile->memory_summary),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        if ($readyForDiagnosis) {
            $update['last_diagnosis_json'] = json_encode([
                'diagnosis' => $diagnosis,
                'gaps' => $this->_normalizeList(isset($structured['gaps']) ? $structured['gaps'] : []),
                'priority_actions' => $this->_normalizeList(isset($structured['priority_actions']) ? $structured['priority_actions'] : [], 3),
                'suggested_tools' => $this->_normalizeList(isset($structured['suggested_tools']) ? $structured['suggested_tools'] : []),
                'escalation' => isset($structured['escalation']) ? $structured['escalation'] : []
            ]);
            $update['last_plan_json'] = json_encode($plan);
        }

        $this->hustler_profile_m->upsert_profile($profile->hustler_investor_id, $update);
        return $this->hustler_profile_m->get_by_investor($profile->hustler_investor_id);
    }

    private function _syncActionItems($profileID, $structured)
    {
        if (!is_array($structured) || empty($structured)) {
            $items = $this->hustler_action_item_m->get_for_profile($profileID);
            $mapped = [];
            if (customCompute($items)) {
                foreach ($items as $item) {
                    $mapped[] = [
                        'item_type' => $item->item_type,
                        'title' => $item->title,
                        'description' => $item->description,
                        'status' => $item->status
                    ];
                }
            }

            return $mapped;
        }

        $rows = [];

        if (isset($structured['weekly_plan']['tasks']) && is_array($structured['weekly_plan']['tasks'])) {
            $order = 1;
            foreach ($structured['weekly_plan']['tasks'] as $task) {
                $title = '';
                $description = '';
                if (is_array($task)) {
                    $title = isset($task['title']) ? trim((string) $task['title']) : '';
                    $description = isset($task['description']) ? trim((string) $task['description']) : '';
                } else {
                    $title = trim((string) $task);
                }

                if ($title === '') {
                    continue;
                }

                $rows[] = [
                    'hustler_founder_profile_id' => $profileID,
                    'item_type' => 'task',
                    'title' => $title,
                    'description' => $description,
                    'status' => 'todo',
                    'sort_order' => $order++
                ];
            }
        }

        if (isset($structured['weekly_plan']['milestones']) && is_array($structured['weekly_plan']['milestones'])) {
            $order = 1;
            foreach ($structured['weekly_plan']['milestones'] as $milestone) {
                $title = '';
                $description = '';
                if (is_array($milestone)) {
                    $title = isset($milestone['title']) ? trim((string) $milestone['title']) : '';
                    $description = isset($milestone['description']) ? trim((string) $milestone['description']) : '';
                } else {
                    $title = trim((string) $milestone);
                }

                if ($title === '') {
                    continue;
                }

                $rows[] = [
                    'hustler_founder_profile_id' => $profileID,
                    'item_type' => 'milestone',
                    'title' => $title,
                    'description' => $description,
                    'status' => 'planned',
                    'sort_order' => $order++
                ];
            }
        }

        $this->hustler_action_item_m->replace_for_profile($profileID, $rows);

        $items = $this->hustler_action_item_m->get_for_profile($profileID);
        $mapped = [];
        if (customCompute($items)) {
            foreach ($items as $item) {
                $mapped[] = [
                    'item_type' => $item->item_type,
                    'title' => $item->title,
                    'description' => $item->description,
                    'status' => $item->status
                ];
            }
        }

        return $mapped;
    }

    private function _profileViewData($profile)
    {
        return [
            'founder_name' => (string) $profile->founder_name,
            'profile_photo_url' => isset($profile->profile_photo_url) ? (string) $profile->profile_photo_url : '',
            'company_logo_url' => isset($profile->company_logo_url) ? (string) $profile->company_logo_url : '',
            'company_name' => (string) $profile->company_name,
            'target_customer_profile' => isset($profile->target_customer_profile) ? (string) $profile->target_customer_profile : '',
            'stage_label' => (string) $profile->stage_label,
            'idea_summary' => (string) $profile->idea_summary
        ];
    }

    private function _countUserMessages($profileID)
    {
        return (int) $this->db->where('hustler_founder_profile_id', $profileID)
            ->where('role', 'user')
            ->count_all_results('hustler_conversations');
    }

    private function _shouldUseDiscoveryMode($profile, $userMessageCount)
    {
        $founderName = trim((string) $profile->founder_name);
        $company = trim((string) $profile->company_name);
        $logo = trim((string) $profile->company_logo_url);
        $idea = trim((string) $profile->idea_summary);
        $icp = trim((string) $profile->target_customer_profile);

        return ($founderName === '' || $company === '' || $logo === '' || $idea === '' || $icp === '');
    }

    private function _getCurrentActionItems($profileID)
    {
        $items = $this->hustler_action_item_m->get_for_profile($profileID);
        $mapped = [];
        if (customCompute($items)) {
            foreach ($items as $item) {
                $mapped[] = [
                    'item_type' => $item->item_type,
                    'title' => $item->title,
                    'description' => $item->description,
                    'status' => $item->status
                ];
            }
        }

        return $mapped;
    }

    private function _marketAccessGate($profile)
    {
        $messageCount = (int) $this->db->where('hustler_founder_profile_id', (int) $profile->hustler_founder_profile_id)
            ->count_all_results('hustler_conversations');

        $founderName = trim((string) $profile->founder_name);
        $companyName = trim((string) $profile->company_name);
        $companyLogo = trim((string) $profile->company_logo_url);
        $ideaSummary = trim((string) $profile->idea_summary);
        $targetCustomer = trim((string) $profile->target_customer_profile);

        $hasCoreInfo = ($founderName !== '' && $companyName !== '' && $companyLogo !== '' && $ideaSummary !== '' && $targetCustomer !== '');
        if (!$hasCoreInfo && $messageCount < 5) {
            return [
                'allowed' => false,
                'reason' => 'Market Access unlocks after 5 chat messages or once founder name, company name, company logo, idea, and target customer profile are captured.'
            ];
        }

        return ['allowed' => true, 'reason' => ''];
    }

    private function _extractTargetCustomerProfile($founderProfile, $structured, $currentValue = '')
    {
        $candidates = [
            $this->_stringFromArray($founderProfile, 'target_customer_profile', ''),
            $this->_stringFromArray($founderProfile, 'ideal_customer_profile', ''),
            $this->_stringFromArray($founderProfile, 'icp', ''),
            $this->_stringFromArray(is_array($structured) ? $structured : [], 'target_customer_profile', ''),
            $this->_stringFromArray(is_array($structured) ? $structured : [], 'ideal_customer_profile', ''),
            $this->_stringFromArray(is_array($structured) ? $structured : [], 'icp', ''),
            (string) $currentValue
        ];

        foreach ($candidates as $candidate) {
            $candidate = trim((string) $candidate);
            if ($candidate !== '') {
                return $candidate;
            }
        }

        return '';
    }

    private function _buildReadableReplyFromStructured($structured, $fallbackText, $discoveryMode = false)
    {
        if (!is_array($structured) || empty($structured)) {
            return $fallbackText;
        }

        if ($discoveryMode) {
            if (isset($structured['memory_summary'])) {
                $memory = trim((string) $structured['memory_summary']);
                if ($memory !== '') {
                    return $memory;
                }
            }
            return $fallbackText;
        }

        $lines = [];
        if (isset($structured['diagnosis']['current_status'])) {
            $lines[] = 'Status: ' . trim((string) $structured['diagnosis']['current_status']);
        }
        if (isset($structured['diagnosis']['bottleneck_identification'])) {
            $lines[] = 'Bottleneck: ' . trim((string) $structured['diagnosis']['bottleneck_identification']);
        }

        if (isset($structured['priority_actions']) && is_array($structured['priority_actions']) && customCompute($structured['priority_actions'])) {
            $lines[] = '';
            $lines[] = 'Top next steps:';
            $index = 1;
            foreach ($structured['priority_actions'] as $action) {
                $action = trim((string) $action);
                if ($action === '') {
                    continue;
                }
                $lines[] = $index . '. ' . $action;
                $index++;
                if ($index > 3) {
                    break;
                }
            }
        }

        $text = trim(implode("\n", $lines));
        return $text !== '' ? $text : $fallbackText;
    }

    private function _extractRequestedPostCount($focus, $default = 30)
    {
        $count = (int) $default;
        if (preg_match('/\b([1-9][0-9]?)\s+(?:social\s+)?posts?\b/i', (string) $focus, $matches)) {
            $candidate = isset($matches[1]) ? (int) $matches[1] : $count;
            if ($candidate >= 6 && $candidate <= 40) {
                $count = $candidate;
            }
        }

        return $count;
    }

    private function _repairMarketAccessJson($apiKey, $rawText, $model)
    {
        $payload = [
            'model' => $model,
            'input' => [
                [
                    'role' => 'user',
                    'content' => "Convert this content into strict JSON with keys: market_overview, ideal_customer_profile, instagram_profile, competitor_patterns, distribution_angles, social_posts, funnel_suggestions. social_posts can be strings or objects with caption and image_prompt. funnel_suggestions should be exactly 3 objects. Do not add any explanation.\n\nContent:\n" . (string) $rawText
                ]
            ],
            'instructions' => 'Output JSON only.',
            'max_output_tokens' => 1600,
            'temperature' => 0.2
        ];

        $response = $this->_callOpenAI($apiKey, $payload);
        if (!$response['ok']) {
            return [];
        }

        $text = $this->_extractResponseText($response['data']);
        return $this->_decodeStructuredResponse($text);
    }

    private function _getOpenAIKey()
    {
        $openaiKey = getenv('OPENAI_API_KEY');
        if (empty($openaiKey) && isset($_SERVER['OPENAI_API_KEY'])) {
            $openaiKey = $_SERVER['OPENAI_API_KEY'];
        }
        if (empty($openaiKey) && isset($_ENV['OPENAI_API_KEY'])) {
            $openaiKey = $_ENV['OPENAI_API_KEY'];
        }
        if (empty($openaiKey)) {
            $secretPath = APPPATH . 'config/openai_secret.php';
            if (file_exists($secretPath)) {
                $secret = include $secretPath;
                if (is_array($secret) && !empty($secret['openai_api_key'])) {
                    $openaiKey = $secret['openai_api_key'];
                }
            }
        }

        return is_string($openaiKey) ? $openaiKey : '';
    }

    private function _callOpenAI($apiKey, $payload)
    {
        $ch = curl_init('https://api.openai.com/v1/responses');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $result = curl_exec($ch);
        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['ok' => false, 'error' => $error];
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $data = json_decode($result, true);

        if ($status >= 400 || !is_array($data)) {
            $message = isset($data['error']['message']) ? $data['error']['message'] : 'OpenAI request failed.';
            return ['ok' => false, 'error' => $message];
        }

        return ['ok' => true, 'data' => $data];
    }

    private function _extractResponseText($data)
    {
        if (!isset($data['output']) || !is_array($data['output'])) {
            return '';
        }
        foreach ($data['output'] as $item) {
            if (isset($item['type']) && $item['type'] === 'message' && isset($item['content'])) {
                foreach ($item['content'] as $content) {
                    if (isset($content['type']) && $content['type'] === 'output_text') {
                        return $content['text'];
                    }
                }
            }
        }
        return '';
    }

    private function _friendlyOpenAIError($message)
    {
        $safeDefault = 'Hustler AI is temporarily unavailable. Please try again in a minute.';
        $message = (string) $message;
        if (stripos($message, 'Incorrect API key') !== false || stripos($message, 'authentication') !== false) {
            return 'AI service is not configured correctly. Please verify the shared OpenAI key.';
        }
        if (stripos($message, 'rate limit') !== false) {
            return 'Hustler AI is busy right now. Please try again shortly.';
        }
        if (stripos($message, 'insufficient_quota') !== false) {
            return 'Hustler AI is temporarily unavailable due to billing limits.';
        }
        return $safeDefault;
    }

    private function _decodeStructuredResponse($text)
    {
        $decoded = json_decode($text, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        if ($start === false || $end === false || $end <= $start) {
            return [];
        }

        $json = substr($text, $start, ($end - $start + 1));
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function _decodeJsonField($value)
    {
        $decoded = json_decode((string) $value, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function _normalizeList($value, $limit = 10)
    {
        $items = [];
        if (is_array($value)) {
            foreach ($value as $entry) {
                if (is_array($entry)) {
                    $entry = isset($entry['title']) ? $entry['title'] : json_encode($entry);
                }

                $entry = trim((string) $entry);
                if ($entry !== '') {
                    $items[] = $entry;
                }

                if (count($items) >= $limit) {
                    break;
                }
            }
        }

        return $items;
    }

    private function _stringFromArray($array, $key, $fallback = '')
    {
        if (!is_array($array) || !isset($array[$key])) {
            return (string) $fallback;
        }

        $value = trim((string) $array[$key]);
        return $value !== '' ? $value : (string) $fallback;
    }

    private function _stringOrDefault($value, $fallback = '')
    {
        $value = trim((string) $value);
        return $value !== '' ? $value : (string) $fallback;
    }

    private function _stringOrFallback($array, $key)
    {
        if (!is_array($array) || !isset($array[$key])) {
            return '';
        }

        return $this->_normalizeText($array[$key]);
    }

    private function _textFieldOrFallback($array, $key)
    {
        if (!is_array($array) || !isset($array[$key])) {
            return '';
        }

        return $this->_normalizeText($array[$key]);
    }

    private function _normalizeText($value)
    {
        if (is_array($value)) {
            $parts = [];
            foreach ($value as $item) {
                if (is_array($item)) {
                    $parts[] = $this->_normalizeText($item);
                } else {
                    $item = trim((string) $item);
                    if ($item !== '') {
                        $parts[] = $item;
                    }
                }
            }

            return trim(implode("\n", $parts));
        }

        if (is_object($value)) {
            return trim(json_encode($value));
        }

        return trim((string) $value);
    }

    private function _extractSocialPosts($structured, $limit = 30)
    {
        $posts = [];
        $rows = isset($structured['social_posts']) && is_array($structured['social_posts']) ? $structured['social_posts'] : [];

        foreach ($rows as $row) {
            if (count($posts) >= $limit) {
                break;
            }

            if (is_array($row)) {
                $caption = isset($row['caption']) ? trim((string) $row['caption']) : '';
                $prompt = isset($row['image_prompt']) ? trim((string) $row['image_prompt']) : '';
                if ($caption === '') {
                    $caption = isset($row['text']) ? trim((string) $row['text']) : '';
                }
                if ($prompt === '') {
                    $prompt = 'Create a clean social media post visual for: ' . $caption;
                }
            } else {
                $caption = trim((string) $row);
                $prompt = 'Create a clean social media post visual for: ' . $caption;
            }

            if ($caption === '') {
                continue;
            }

            $posts[] = [
                'caption' => $caption,
                'image_prompt' => $prompt
            ];
        }

        return $posts;
    }

    private function _fallbackSocialPosts($profile, $limit = 6)
    {
        $company = trim((string) $profile->company_name);
        if ($company === '') {
            $company = 'your startup';
        }
        $idea = trim((string) $profile->idea_summary);
        $items = [];
        for ($i = 1; $i <= $limit; $i++) {
            $caption = "Post {$i}: {$company} helps customers with {$idea}.";
            $items[] = [
                'caption' => $caption,
                'image_prompt' => "Instagram square post concept {$i} for {$company}. Clean modern style. Topic: {$idea}."
            ];
        }
        return $items;
    }

    private function _extractInstagramProfile($structured, $profile, $socialPosts)
    {
        $data = isset($structured['instagram_profile']) && is_array($structured['instagram_profile']) ? $structured['instagram_profile'] : [];
        $company = trim((string) $profile->company_name);
        $company = $company !== '' ? $company : 'hatchersfounder';

        $username = isset($data['username']) ? trim((string) $data['username']) : '';
        if ($username === '') {
            $username = strtolower(preg_replace('/[^a-z0-9]+/i', '', $company));
        }

        $displayName = isset($data['display_name']) ? trim((string) $data['display_name']) : '';
        if ($displayName === '') {
            $displayName = $company;
        }

        $bio = isset($data['bio']) ? trim((string) $data['bio']) : '';
        if ($bio === '') {
            $bio = trim((string) $profile->idea_summary);
        }

        $website = isset($data['website']) ? trim((string) $data['website']) : 'https://hatchers.ai';
        $followers = isset($data['followers']) ? (int) $data['followers'] : (1200 + (customCompute($socialPosts) * 50));
        $following = isset($data['following']) ? (int) $data['following'] : 30;
        $logo = isset($data['logo_url']) ? trim((string) $data['logo_url']) : '';
        if ($logo === '') {
            $logo = isset($profile->company_logo_url) ? trim((string) $profile->company_logo_url) : '';
        }

        return [
            'username' => $username,
            'display_name' => $displayName,
            'bio' => $bio,
            'website' => $website,
            'followers' => $followers,
            'following' => $following,
            'logo_url' => $logo,
            'posts' => customCompute($socialPosts)
        ];
    }

    private function _extractFunnels($structured, $profile)
    {
        $raw = isset($structured['funnel_suggestions']) && is_array($structured['funnel_suggestions']) ? $structured['funnel_suggestions'] : [];
        $mapped = [];
        $templatePool = ['awareness', 'conversion', 'lead_capture', 'dark_flow'];
        shuffle($templatePool);
        $selectedTemplates = array_slice($templatePool, 0, 3);
        $poolIndex = 0;
        foreach ($raw as $row) {
            if (!is_array($row)) {
                continue;
            }

            $steps = [];
            if (isset($row['steps']) && is_array($row['steps'])) {
                foreach ($row['steps'] as $s) {
                    $s = trim((string) $s);
                    if ($s !== '') {
                        $steps[] = $s;
                    }
                    if (count($steps) >= 4) {
                        break;
                    }
                }
            }

            $designType = $selectedTemplates[$poolIndex % count($selectedTemplates)];
            $poolIndex++;

            $mapped[] = [
                'design_type' => $designType,
                'title' => isset($row['title']) ? trim((string) $row['title']) : '',
                'subtitle' => isset($row['subtitle']) ? trim((string) $row['subtitle']) : '',
                'steps' => $steps,
                'cta' => isset($row['cta']) ? trim((string) $row['cta']) : ''
            ];
            if (count($mapped) >= 3) {
                break;
            }
        }

        if (count($mapped) < 3) {
            $company = trim((string) $profile->company_name);
            if ($company === '') {
                $company = 'Your Company';
            }
            $fallbacks = [
                [
                    'design_type' => 'awareness',
                    'title' => 'Awareness Funnel',
                    'subtitle' => "Reach the right audience for {$company}",
                    'steps' => ['Big hook content', 'Lead magnet CTA', 'Social proof examples', 'Book strategy call'],
                    'cta' => 'Get the free blueprint'
                ],
                [
                    'design_type' => 'conversion',
                    'title' => 'Conversion Funnel',
                    'subtitle' => 'Turn warm leads into buyers',
                    'steps' => ['Offer promise', 'Risk reversal', 'Urgency push', 'Direct purchase CTA'],
                    'cta' => 'Claim your offer'
                ],
                [
                    'design_type' => 'lead_capture',
                    'title' => 'Lead Capture Funnel',
                    'subtitle' => 'Capture and qualify quality leads',
                    'steps' => ['Traffic campaign', 'Landing page', 'Qualification form', 'Follow-up sequence'],
                    'cta' => 'Start lead capture'
                ],
                [
                    'design_type' => 'dark_flow',
                    'title' => 'First Customer Funnel',
                    'subtitle' => 'From click to paying customer in one flow',
                    'steps' => ['Traffic source', 'Landing page hook', 'Qualification call', 'Close with urgency'],
                    'cta' => 'Launch this funnel'
                ]
            ];
            foreach ($fallbacks as $f) {
                if (count($mapped) >= 3) {
                    break;
                }
                $mapped[] = $f;
            }
        }

        return array_slice($mapped, 0, 3);
    }

    private function _generateSocialPostImages($apiKey, $socialPosts, $profile, $limit = 9)
    {
        $images = [];
        $count = 0;
        foreach ($socialPosts as $post) {
            if ($count >= $limit) {
                break;
            }

            $prompt = isset($post['image_prompt']) ? (string) $post['image_prompt'] : '';
            $caption = isset($post['caption']) ? (string) $post['caption'] : '';
            if (trim($prompt) === '') {
                $prompt = 'Create a startup social media visual. Caption context: ' . $caption;
            }

            $fullPrompt = "Create a high-quality square social media image."
                . " Brand context: " . (string) $profile->company_name
                . ". Founder idea context: " . (string) $profile->idea_summary
                . ". Target customer profile: " . (string) $profile->target_customer_profile
                . ". Image brief: " . $prompt
                . ". Do not include excessive text in the image.";

            $result = $this->_callOpenAIImage($apiKey, $fullPrompt);
            if (!$result['ok']) {
                continue;
            }

            $filename = $this->_storeGeneratedImage($result['b64'], $profile->hustler_founder_profile_id, $count + 1);
            if ($filename === '') {
                continue;
            }

            $images[] = [
                'caption' => $caption,
                'image_url' => base_url('uploads/hustler_posts/' . $filename)
            ];
            $count++;
        }

        return $images;
    }

    private function _callOpenAIImage($apiKey, $prompt)
    {
        $payload = [
            'model' => 'gpt-image-1',
            'prompt' => $prompt,
            'size' => '512x512'
        ];

        $ch = curl_init('https://api.openai.com/v1/images/generations');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $result = curl_exec($ch);
        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['ok' => false, 'error' => $error];
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $data = json_decode($result, true);

        if ($status >= 400 || !is_array($data)) {
            return ['ok' => false, 'error' => 'Image generation failed.'];
        }

        if (!isset($data['data'][0]['b64_json'])) {
            return ['ok' => false, 'error' => 'No image payload returned.'];
        }

        return ['ok' => true, 'b64' => $data['data'][0]['b64_json']];
    }

    private function _storeGeneratedImage($b64, $profileID, $index)
    {
        $dir = FCPATH . 'uploads/hustler_posts/';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        if (!is_dir($dir) || !is_writable($dir)) {
            return '';
        }

        $binary = base64_decode((string) $b64);
        if ($binary === false) {
            return '';
        }

        $filename = 'profile_' . (int) $profileID . '_' . date('YmdHis') . '_' . (int) $index . '.png';
        $saved = @file_put_contents($dir . $filename, $binary);
        if ($saved === false) {
            return '';
        }

        @chmod($dir . $filename, 0644);
        return $filename;
    }

    private function _storeProfileMediaFile($file, $profileID, $mediaType)
    {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return '';
        }

        $maxBytes = 5 * 1024 * 1024;
        $size = isset($file['size']) ? (int) $file['size'] : 0;
        if ($size <= 0 || $size > $maxBytes) {
            return '';
        }

        $ext = strtolower(pathinfo(isset($file['name']) ? $file['name'] : '', PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $allowed, true)) {
            return '';
        }

        $dir = FCPATH . 'uploads/hustler_profiles/';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        if (!is_dir($dir) || !is_writable($dir)) {
            return '';
        }

        $prefix = $mediaType === 'company_logo' ? 'company_logo' : 'founder_photo';
        $filename = $prefix . '_' . (int) $profileID . '_' . date('YmdHis') . '.' . $ext;
        $full = $dir . $filename;

        if (!@move_uploaded_file($file['tmp_name'], $full)) {
            return '';
        }

        @chmod($full, 0644);
        return base_url('uploads/hustler_profiles/' . $filename);
    }

    private function _hash($string)
    {
        return hash('sha512', config_item('encryption_key') . $string);
    }

    private function _json($payload)
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }
}
