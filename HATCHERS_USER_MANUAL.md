# Hatchers LMS User Manual

This guide explains how to use the Hatchers platform for **Founders**, **Mentors**, and **Super Admins**.

---

## Founder Guide

### 1. Your Home Dashboard
After login, you land on the **Home** dashboard. It includes:
- **Left navigation**: Quick access to your journey sections (Home, Launch Plan, AI Tools, Learning Plan, Mentoring).
- **Center timeline**: What’s included in your day (mentoring sessions, learning items, tasks).
- **Right panel**: Calendar + AI Tools list.
- **Bottom AI chat**: Ask the Hatchers AI assistant for help.

### 2. Understanding Your Day
The center feed is split into:
- **Mentoring**: Next session with your mentor.
- **Learning**: Your next learning lesson.
- **Tasks**: Action items you must complete.

### 3. Using the Calendar
The right panel calendar highlights:
- Mentoring sessions
- Learning sessions
- Task due dates

Clicking a date shows what’s scheduled that day.

### 4. Using Hatchers AI Assistant
At the bottom of the home page:
- Type your question in the chat box.
- Click the arrow or press **Enter**.
- Hatchers AI will respond based on your milestones, tasks, and progress.

The AI uses your current program context to give personalized answers.

---

## Mentor Guide

### 1. Mentor Dashboard
After login, you see your **Founders list**.
- Click any founder to open the **full detail page**.

### 2. Founder Detail Page
On the detail page, you manage:
- **Milestones**
- **Mentoring Meetings**
- **Learning Items**
- **Tasks**

### 3. Add a Milestone
1. Open founder detail page.
2. Scroll to **Milestones**.
3. Fill in:
   - Title
   - Description
   - Due date (optional)
4. Click **Add Milestone**.

### 4. Schedule a Mentoring Meeting
1. In **Mentoring Meetings**, fill:
   - Start time
   - End time (optional)
   - Notes (optional)
2. Click **Add Meeting**.

Meetings appear on the founder’s calendar automatically.

### 5. Add Learning
1. In **Learning**, fill:
   - Lesson title
   - Subtitle
   - Start time (optional)
2. Click **Add Lesson**.

### 6. Add Tasks
1. In **Tasks**, fill:
   - Task title
   - Description
   - Optional milestone
   - Due date
2. Click **Add Task**.

Tasks show on the founder’s dashboard and calendar.

---

## Super Admin Guide

### 1. Main Control Pages
You control the platform through:
- **Assignments**: `/hatchersadmin/assignments`
- **Profiles**: `/hatchersadmin/profiles`
- **Navigation**: `/hatchersadmin/nav`
- **AI Settings**: `/hatchersadmin/ai`

### 2. Create Founders and Mentors
Go to **Profiles** page:
- Fill the **Create Founder** form
- Fill the **Create Mentor** form

Notes:
- Founders automatically get a guardian record (required by the system).
- You can edit or delete users later.

### 3. Assign Mentors to Founders
Go to **Assignments**:
1. Find a founder.
2. Select a mentor.
3. Click **Save**.

Mentor–Founder mapping is **1:1**.

### 4. Bulk Import (CSV)
Go to **Profiles** → Bulk Import:
1. Download a template
2. Upload your CSV
3. Preview validation
4. Confirm import

Templates:
- Founders CSV: `name, email, phone, username, password, classesID, sectionID, roll`
- Mentors CSV: `name, email, phone, username, password, sex, dob, designation`

### 5. Edit or Delete Profiles
From **Profiles**:
- Click **Edit** to update accounts
- Click **Delete** to remove accounts

Safety:
- Founders cannot be deleted if they have assigned mentor, tasks, meetings, learning, milestones, or AI history.
- Mentors cannot be deleted if they are assigned to founders.

### 6. Manage Navigation Items
Go to **Navigation**:
- Add, update, hide, or delete left menu items.
- Add, update, hide, or delete AI tools in the right panel.

### 7. Configure Hatchers AI Assistant
Go to **AI Settings**:
- Enter OpenAI API key
- Update system prompt (main teaching guidance)
- Update guidelines, model, temperature, max tokens

Changes apply immediately to all founders.

---

## Quick Troubleshooting

- **Founder can’t chat with AI**  
  Ensure OpenAI key is set in `/hatchersadmin/ai`.

- **Mentor can’t access founder**  
  Confirm assignment in `/hatchersadmin/assignments`.

- **Import preview shows errors**  
  Fix CSV missing fields or duplicate usernames/emails.

---

## Support Notes

If you want to customize workflows or add new modules, the system is modular and can be extended. We can add:
- Custom founder journeys
- Mentor dashboards with analytics
- Automated reminders and notifications

