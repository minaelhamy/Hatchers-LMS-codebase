# Hatchers LMS System Guide

## Purpose

Hatchers LMS is a founder-mentor operating platform built to support the Hatchers AI program.

The core purpose of the system is to help mentors guide founders through a six-month journey using:

- weekly tasks
- milestones
- meetings
- learning resources
- founder-mentor chat
- an AI assistant connected to founder context

This system is no longer treated as a traditional school management product in practical use. The legacy codebase still exists underneath, but the active Hatchers experience is centered around founder execution and mentor communication.

## Core Roles

There are 3 active operational roles in the Hatchers system.

### Super Admin

The super admin controls onboarding, assignments, AI settings, and platform configuration.

The super admin is responsible for:

- creating founder profiles
- creating mentor profiles
- recording founder business context through the company brief
- assigning founders to mentors
- controlling AI settings
- managing external AI tool links shown in the shell

The super admin does not usually manage the founder week-to-week after assignment. Their role is system control and setup.

### Mentor

The mentor is the operator of the founder journey.

The mentor is responsible for:

- seeing all founders assigned to them
- reviewing each founder’s progress
- scheduling and managing meetings
- assigning milestones
- assigning tasks
- assigning learning resources
- chatting with founders
- responding to founder requests and reschedule requests

The mentor dashboard is designed to function as a portfolio view across all their active founders.

### Founder

The founder is the guided user in the system.

The founder is responsible for:

- viewing what is scheduled for the week
- reviewing tasks and milestones
- seeing progress for the week
- marking tasks complete
- marking milestones complete
- joining meetings
- accepting or requesting rescheduling of meetings
- chatting with the mentor
- using Hatchers AI for support

The founder experience is intended to feel simple, focused, and execution-oriented.

## Main Product Concept

The main operating concept of the LMS is:

1. Super admin creates the people and relationships.
2. Mentor plans and manages the founder journey.
3. Founder executes the work and communicates with the mentor.
4. AI supports the founder using mentor-written context and real founder data.

This means the center of the product is communication plus execution.

The platform is not primarily about courses, classes, sections, attendance, or school administration anymore.

## Main Workflows

## 1. Super Admin Onboarding Workflow

The onboarding workflow begins with the super admin.

### Founder Onboarding

The super admin creates a founder profile from the Profiles screen.

Founder creation includes:

- founder name
- email
- phone
- username
- password
- demographic/profile fields
- company brief

The company brief is important because it holds the business context for the founder, including things like:

- what the founder is building
- the current stage of the company
- the main challenge or opportunity

This brief helps mentors understand the founder and also improves AI context quality.

### Mentor Onboarding

The super admin also creates mentor accounts from the same Profiles area.

Mentor creation includes:

- mentor name
- email
- phone
- username
- password
- designation
- profile fields

### Founder-Mentor Assignment

After both accounts exist, the super admin goes to the Assignments area and connects each founder to one active mentor.

Important rule:

- a founder has one active mentor at a time

This relationship defines who is allowed to manage the founder’s execution and communication flow.

## 2. Mentor Operating Workflow

After assignment, the mentor becomes the main operating user for the founder.

### Mentor Dashboard

The mentor dashboard shows all assigned founders.

For each founder, the mentor can see summary information such as:

- progress percentage
- open tasks
- completed tasks
- overdue tasks
- milestone count
- next meeting
- founder company brief

This allows the mentor to understand the health of their founder portfolio quickly.

### Opening a Founder Workspace

When the mentor clicks a founder, they enter that founder’s mentoring workspace.

This workspace is the main operating area for:

- meetings
- messages
- milestones
- tasks

This is the place where the mentor actually manages the founder week by week.

### Mentor Actions

Inside the mentoring workspace, the mentor can:

- schedule a meeting
- add a meeting title, description, time, notes, and join link
- review founder meeting requests
- review founder reschedule requests
- send messages to the founder
- create milestones
- create tasks
- link tasks to milestones

The mentor’s job is not just to talk to the founder. The mentor is expected to translate business guidance into clear execution objects inside the LMS.

## 3. Founder Operating Workflow

The founder logs into a home dashboard designed around weekly clarity.

### Founder Home

The founder home includes:

- weekly progress
- mentoring section
- learning section
- task list
- AI assistant

The founder should immediately understand:

- what is coming up
- what must be done
- how much progress has been made
- how to get help

### Founder Launch Plan

The launch plan is the founder’s execution view.

It contains:

- milestones
- tasks
- weekly progress calculation

The founder can:

- open tasks
- mark tasks complete
- reopen tasks if needed
- mark milestones complete
- reopen milestones if needed

This creates visible execution feedback for both founder and mentor.

### Founder Meetings

Founders can interact with meetings through the mentoring workspace.

They can:

- view scheduled meeting timing
- accept meeting timing
- request a reschedule
- propose a new time
- add notes explaining the reschedule

When founders request a reschedule, the mentor should be able to see it in notifications and in the mentoring workspace.

### Founder-Mentor Chat

Founders and mentors can exchange direct messages in the system.

This is designed to reduce the need to move execution conversations outside the LMS.

The message thread is shared per founder-mentor relationship, so both users see the same history.

### Founder AI Assistant

The founder has access to Hatchers AI directly from the founder home.

The AI is intended to help with:

- understanding tasks
- getting unstuck
- asking startup questions
- turning mentor guidance into practical next steps

The AI assistant is not generic by design. It uses founder-specific context so that answers are shaped by the real journey of that founder.

## What Can Be Done With This System

The current system supports these product capabilities.

### Account and Relationship Management

- create founders
- create mentors
- edit founders
- edit mentors
- assign founders to mentors
- update AI settings
- manage external tool links

### Founder Execution Management

- create milestones for founders
- create tasks for founders
- connect tasks to milestones
- mark tasks complete
- mark milestones complete
- show weekly execution progress

### Scheduling and Meetings

- mentors schedule meetings
- founders request meetings
- founders accept meeting timing
- founders request reschedules
- mentors respond to requests
- meetings appear in the calendar context

### Communication

- mentor-founder chat thread
- notifications for important items
- shared mentoring workspace

### Learning Support

- founder learning items
- learning library structure
- support for links, PDFs, YouTube, and external resources

### AI Support

- founder AI chat
- OpenAI-backed assistant
- AI context built from real founder data

## Architecture

## 1. High-Level Architecture

The system follows a classic CodeIgniter MVC structure with a Hatchers-specific product layer added on top of a legacy LMS.

At a high level, the architecture is:

- Controllers: role logic, workflow orchestration, access control
- Models: database access
- Views: Hatchers UI screens
- Shared shell: left nav, right rail, calendar, notifications, AI tools
- Database: founder, mentor, assignment, task, milestone, meeting, learning, AI, chat data

## 2. Role and Authentication Architecture

The system still uses the legacy role model under the hood.

Current role IDs:

- `1` = Super Admin
- `2` = Mentor
- `3` = Founder

Role-based routing is handled mainly from the dashboard entry point.

This means the same login system can land users into different Hatchers experiences depending on role.

## 3. Controller Architecture

The main Hatchers controllers are:

### `Hatchersadmin`

Purpose:

- super admin operations
- onboarding
- founder editing
- mentor editing
- assignment management
- AI settings
- AI tool navigation management

This is the administrative control layer.

### `Dashboard`

Purpose:

- role-aware home experience
- founder home
- mentor home
- super admin home
- founder progress summaries
- mentor portfolio summaries

This is the entry layer for active users.

### `Mentoring`

Purpose:

- mentor-founder communication
- meeting handling
- message thread
- mentor-side milestone creation
- mentor-side task creation
- founder-side meeting acceptance/reschedule flow

This is the core operating workspace.

### `Launchplan`

Purpose:

- founder execution board
- milestone and task review
- weekly progress calculation
- completion handling

This is the execution tracking layer.

### `Learningplan`

Purpose:

- learning resources
- founder lessons
- library display

### `Aiassistant`

Purpose:

- founder-facing AI chat
- OpenAI request handling
- context assembly
- AI conversation history
- structured founder context storage

## 4. View Architecture

The active Hatchers product uses a dedicated shell layout.

The shell provides:

- left sidebar navigation
- main content area
- right-side notifications
- right-side calendar
- right-side AI tools

This shared shell is important because it keeps founder, mentor, and super admin views visually aligned even when the role behavior is different.

The major screens are:

- role-aware home
- mentoring workspace
- launch plan
- learning plan
- admin profiles
- admin assignments
- admin AI settings

## 5. Data Architecture

The system uses a combination of legacy user tables and Hatchers-specific product tables.

### Legacy Identity Tables Still Used

- `student`
  Used for founders

- `teacher`
  Used for mentors

- `studentextend`
  Used for founder extension data
  The founder company brief is currently stored in `studentextend.remarks`

- `studentrelation`
  Kept for compatibility with the legacy LMS

### Hatchers-Specific Product Tables

#### `mentor_founder`

Stores the active mentor-founder assignment.

Main purpose:

- defines who owns which founder relationship

#### `founder_tasks`

Stores founder tasks.

Main purpose:

- weekly execution items
- due dates
- status
- completion metadata

#### `milestone_meta`

Stores milestones for founders.

Main purpose:

- outcome-level goals
- milestone progress state

#### `founder_meetings`

Stores meetings between founder and mentor.

Main purpose:

- scheduled mentoring sessions
- founder requests
- meeting responses
- reschedule workflow

#### `founder_learning`

Stores founder learning items.

Main purpose:

- assigned lessons or learning resources

#### `learning_library`

Stores reusable library items.

Main purpose:

- PDFs
- links
- websites
- other learning content

#### `hatchers_messages`

Stores mentor-founder messages.

Main purpose:

- in-system chat

#### `hatcher_ai_settings`

Stores AI configuration.

Main purpose:

- model
- prompt
- behavior rules

#### `hatcher_ai_conversations`

Stores founder AI conversation history.

Main purpose:

- AI memory per founder

#### `hatcher_ai_context`

Stores summarized structured founder context.

Main purpose:

- goals
- current sprint
- blockers
- progress summary

## 6. AI Architecture

The AI system uses OpenAI as the main provider.

The founder asks questions through the founder home interface.

The backend gathers context from:

- founder profile
- founder company brief
- assigned mentor
- tasks
- milestones
- learning items
- meetings
- mentor-founder messages
- recent AI history

Then that context is sent to OpenAI through the assistant controller.

This gives the AI assistant a much better chance of responding with relevant guidance instead of generic advice.

## 7. Notification Architecture

The shared Hatchers shell contains a right-side notification area.

Notifications are generated dynamically depending on role.

Examples:

- founders see meeting reminders, task reminders, and learning reminders
- mentors see assignment items and meeting/reschedule requests
- super admins see founder account summaries

The notification model is intentionally lightweight and derived from actual product data rather than a large separate notification engine.

## 8. Calendar Architecture

The shell includes a shared calendar view.

Calendar events are assembled from:

- meetings
- task due dates
- milestone due dates
- learning session dates

This gives every role a time-based view into execution.

## Detailed Role-by-Role Guide

## Super Admin Guide

### What the Super Admin Can Do

The super admin can:

- onboard founders
- onboard mentors
- store founder business context
- assign founders to mentors
- configure the AI
- manage tool links
- view system-wide summaries

### What the Super Admin Does Not Usually Do

The super admin does not usually:

- create weekly tasks for founders
- hold the ongoing founder conversation
- manage founder execution day to day

That is the mentor’s role.

### Recommended Super Admin Process

Recommended onboarding sequence:

1. Create mentor profile.
2. Create founder profile.
3. Write the founder company brief.
4. Assign the founder to a mentor.
5. Confirm the mentor can open that founder’s workspace.

## Mentor Guide

### What the Mentor Sees First

The mentor lands on a dashboard showing the full assigned founder portfolio.

This view helps the mentor prioritize:

- which founders are behind
- which founders have overdue work
- which founders have upcoming meetings
- which founders need attention first

### How the Mentor Operates

The mentor selects a founder and works from the mentoring workspace.

From there the mentor can:

- schedule sessions
- message the founder
- set milestones
- set tasks

The mentor should think in the following sequence:

1. Define what outcome matters next.
2. Turn that into a milestone if needed.
3. Break it into weekly tasks.
4. Set a meeting if alignment is needed.
5. Follow up through messages and AI-supported founder execution.

### How the Mentor Uses Founder Progress

Progress should be interpreted as an execution signal, not a vanity number.

The mentor can use it to identify:

- whether a founder is actually completing work
- whether workload is too heavy
- whether meetings need to be scheduled more often
- whether the founder needs better task clarity

## Founder Guide

### What the Founder Sees First

The founder lands on a home screen showing:

- weekly progress
- meetings
- learning
- tasks
- AI support

The goal is clarity without noise.

### How the Founder Uses the System

The founder should primarily use the LMS to:

- see what must be done this week
- keep up with mentor meetings
- complete assigned work
- ask questions when blocked
- stay aligned with the mentor

### How the Founder Uses AI

The founder can ask AI:

- how to complete a task
- how to think through a business challenge
- how to break a milestone into steps
- how to make progress before the next mentor session

The AI is most useful when the mentor has written clear founder context and assigned structured tasks.

## Practical Product Boundaries

The system is built around founder guidance and communication.

It is not intended to actively use school-era modules such as:

- attendance management
- leave management
- transport
- classic syllabus/section operations
- parent/guardian workflows

Some of those legacy structures still exist for compatibility in the codebase, but they are not part of the active Hatchers product workflow.

## Current Technical Notes

### Founder Company Brief

The founder company brief is currently stored in `studentextend.remarks`.

This works, but in a future cleanup it would be cleaner to move this into a dedicated founder-profile field or Hatchers-specific founder profile table.

### Assignment Model

The system is currently designed around one active mentor per founder.

That simplifies:

- permissions
- ownership
- communication flow
- progress interpretation

### AI Provider

OpenAI is the active AI provider in the current architecture.

### Legacy Compatibility

The codebase is still built on top of a legacy LMS structure.

That means some compatibility fields such as class/section still exist under the hood, even though the active Hatchers product tries not to expose them as part of normal operations.

## Recommended Future Improvements

Suggested next improvements for the system:

- move company brief into a dedicated founder profile field or table
- create a dedicated notification table if notification history becomes important
- create task and milestone comments if richer execution history is needed
- add richer learning-library management UI for PDFs and links
- add meeting reminder jobs for same-day notifications
- add founder analytics over time, not only weekly completion
- fully remove remaining legacy LMS menu exposure and unused modules

## Summary

Hatchers LMS works as a role-based founder execution platform.

The super admin creates people and relationships.
The mentor operates the journey.
The founder executes weekly work.
The AI assistant supports the founder using real context.

The architecture is centered around:

- role-aware dashboards
- mentor-founder assignments
- tasks
- milestones
- meetings
- learning
- chat
- OpenAI-based assistance

This makes the LMS a guided execution system for founders rather than a generic educational platform.
