# REST API Specification & Endpoint Documentation

This document defines the complete RESTful API contract for the **Portfolio CMS**. All endpoints return and accept JSON, communicating with the backend controllers and models connected to MySQL via PDO (`portfolio_cms` database).

---

## 1. Global Conventions

* **Base URL**: `/api` (e.g. `http://localhost/myprojects/ads_portfolio_proj/api`)
* **Headers**:
  * `Content-Type: application/json`
  * `Accept: application/json`
* **Standard Response Envelope**:
  ```json
  {
    "success": true,
    "message": "Operation successful description",
    "data": {}
  }
  ```
* **Error Response Envelope**:
  ```json
  {
    "success": false,
    "message": "Validation error or error description",
    "errors": []
  }
  ```
* **HTTP Status Codes**:
  * `200 OK`: Request succeeded.
  * `201 Created`: Resource successfully created.
  * `400 Bad Request`: Validation failure or missing payload.
  * `404 Not Found`: Resource with specified ID does not exist.
  * `405 Method Not Allowed`: HTTP method is unsupported for this endpoint.
  * `500 Internal Server Error`: Database query exception or server failure.

---

## 2. Architectural File Mapping

| Entity / Domain | Model File | Controller File | Primary Database Table |
| :--- | :--- | :--- | :--- |
| **Site Settings** | `App/Models/SiteSetting.php` | `App/Controller/SiteSettingController.php` | `site_settings` |
| **Page Sections** | `App/Models/PageSection.php` | `App/Controller/PageSectionController.php` | `page_sections` |
| **Basic Info** | `App/Models/BasicInfo.php` | `App/Controller/BasicInfoController.php` | `my_basic_info` |
| **Contact Channels**| `App/Models/ContactInfo.php` | `App/Controller/ContactInfoController.php` | `my_contact_info` |
| **Projects** | `App/Models/Project.php` | `App/Controller/ProjectController.php` | `my_projects` |
| **Work Experience** | `App/Models/Experience.php` | `App/Controller/ExperienceController.php` | `my_experience` |
| **Skills Matrix** | `App/Models/Skill.php` | `App/Controller/SkillController.php` | `my_skills` |
| **Education** | `App/Models/Education.php` | `App/Controller/EducationController.php` | `my_education` |
| **Certificates** | `App/Models/Certificate.php` | `App/Controller/CertificateController.php` | `my_certificates` |
| **Inquiries Inbox** | `App/Models/ContactInquiry.php` | `App/Controller/ContactInquiryController.php` | `contact_inquiries` |
| **API Entrypoint** | N/A | `api/index.php` (Front Controller Router) | N/A |

---

## 3. Detailed API Endpoints

### 3.1 Site Settings (`/api/settings`)
Manages global theme preferences, browser tab title, and availability badge status.

#### `GET /api/settings`
* **Description**: Retrieve all site settings key-value pairs.
* **Controller**: `SiteSettingController@getAll`
* **Response** `200 OK`:
  ```json
  {
    "success": true,
    "data": {
      "theme": "monochrome",
      "site_title": "Jhon Clein Pagarogan — Full-Stack Developer",
      "availability_badge": "Available for Select Projects & Full-Time Roles"
    }
  }
  ```

#### `PUT /api/settings`
* **Description**: Update one or more global settings (e.g. change color theme to `midnight` or update availability text).
* **Controller**: `SiteSettingController@update`
* **Request Payload**:
  ```json
  {
    "theme": "midnight",
    "site_title": "Jhon Clein Pagarogan — Portfolio",
    "availability_badge": "Open for Q4 Contracts"
  }
  ```
* **Response** `200 OK`:
  ```json
  {
    "success": true,
    "message": "Settings updated successfully."
  }
  ```

---

### 3.2 Page Sections & Hybrid Layout (`/api/sections`)
Controls the hybrid dynamic view engine (drag/reorder, visibility toggle, custom section headers).

#### `GET /api/sections`
* **Description**: Retrieve all sections sorted by `order_index ASC`.
* **Query Params**: `?active_only=1` (optional filter for public site).
* **Controller**: `PageSectionController@getAll`
* **Response** `200 OK`:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 1,
        "section_key": "about",
        "nav_label": "About",
        "kicker": "Get to Know Me",
        "title": "About Me",
        "subtitle": "",
        "is_visible": 1,
        "order_index": 1
      }
    ]
  }
  ```

#### `PUT /api/sections/{id}`
* **Description**: Update section titles, kicker, subtitle, or individual visibility.
* **Controller**: `PageSectionController@update`
* **Request Payload**:
  ```json
  {
    "nav_label": "About",
    "kicker": "Get to Know Me",
    "title": "Biography & Background",
    "subtitle": "Software engineering philosophy.",
    "is_visible": 1
  }
  ```
* **Response** `200 OK`.

#### `PUT /api/sections/order`
* **Description**: Batch update section display orders and visibility states.
* **Controller**: `PageSectionController@updateOrder`
* **Request Payload**:
  ```json
  {
    "sections": [
      {"section_key": "projects", "order_index": 1, "is_visible": 1},
      {"section_key": "about", "order_index": 2, "is_visible": 1},
      {"section_key": "experience", "order_index": 3, "is_visible": 1}
    ]
  }
  ```
* **Response** `200 OK`.

---

### 3.3 Basic & Profile Information (`/api/basic-info`)

#### `GET /api/basic-info`
* **Description**: Get profile information, hero text, and narrative bio paragraphs.
* **Controller**: `BasicInfoController@getInfo`
* **Response** `200 OK`:
  ```json
  {
    "success": true,
    "data": {
      "first_name": "Jhon Clein",
      "last_name": "Pagarogan",
      "middle_name": "",
      "birth_date": "2004-01-01",
      "role_title": "Full-Stack Web Developer",
      "tagline": "Crafting high-performance web applications...",
      "avatar_url": "Public/assets/images/image.png",
      "resume_url": "Public/assets/Resume/Resume.md",
      "bio_paragraphs": [
        "First paragraph...",
        "Second paragraph..."
      ]
    }
  }
  ```

#### `PUT /api/basic-info`
* **Description**: Update personal profile info, tagline, bio paragraphs, and asset URLs.
* **Controller**: `BasicInfoController@updateInfo`
* **Request Payload**: (matches fields above)
* **Response** `200 OK`.

---

### 3.4 Key Projects (`/api/projects`)

#### `GET /api/projects`
* **Description**: List all projects.
* **Query Params**: `?featured=1` (optional filter).
* **Controller**: `ProjectController@getAll`
* **Response** `200 OK`.

#### `GET /api/projects/{id}`
* **Description**: Get details of a single project by ID.
* **Controller**: `ProjectController@getById`

#### `POST /api/projects`
* **Description**: Create a new project.
* **Controller**: `ProjectController@create`
* **Request Payload**:
  ```json
  {
    "project_name": "NexusCommerce",
    "subtitle": "High-Concurrency E-Commerce & Inventory Engine",
    "description": "Architected a high-concurrency e-commerce API...",
    "technologies": "Laravel 12, PostgreSQL 16, Redis 7, Stripe, Docker",
    "url": "",
    "github_repo": "https://github.com/Nishieeee/e-commerce-sample.git",
    "date_start": "2026-01-01",
    "date_end": "2026-04-01",
    "is_featured": 1,
    "badge": "Flagship Project"
  }
  ```
* **Response** `201 Created`:
  ```json
  {
    "success": true,
    "message": "Project created successfully.",
    "data": { "id": 6 }
  }
  ```

#### `PUT /api/projects/{id}`
* **Description**: Update existing project details.
* **Controller**: `ProjectController@update`
* **Request Payload**: (same fields as POST)
* **Response** `200 OK`.

#### `DELETE /api/projects/{id}`
* **Description**: Delete a project by ID.
* **Controller**: `ProjectController@delete`
* **Response** `200 OK`.

---

### 3.5 Work Experience (`/api/experience`)

#### `GET /api/experience`
* **Description**: List all career experience entries ordered chronologically.
* **Controller**: `ExperienceController@getAll`

#### `POST /api/experience`
* **Description**: Add new work experience entry.
* **Controller**: `ExperienceController@create`
* **Request Payload**:
  ```json
  {
    "job_title": "Freelance Web Developer",
    "company_name": "AfterVa",
    "location": "Remote",
    "description_1": "Architect and deliver responsive web applications...",
    "description_2": "Consult with clients to define specifications...",
    "description_3": "Optimize frontend assets and query performance...",
    "date_start": "2026-05-01",
    "date_end": null,
    "date_display": "May 2026 – Present"
  }
  ```
* **Response** `201 Created`.

#### `PUT /api/experience/{id}`
* **Description**: Update experience entry.
* **Controller**: `ExperienceController@update`

#### `DELETE /api/experience/{id}`
* **Description**: Remove an experience entry.
* **Controller**: `ExperienceController@delete`

---

### 3.6 Skills Matrix (`/api/skills`)

#### `GET /api/skills`
* **Description**: Get all skills grouped by category or as a flat list.
* **Controller**: `SkillController@getAll`
* **Response** `200 OK`.

#### `POST /api/skills`
* **Description**: Add a new skill category block or individual skill.
* **Controller**: `SkillController@create`
* **Request Payload**:
  ```json
  {
    "skill_category": "technical",
    "category_label": "Backend Architecture & APIs",
    "skills_list": "PHP, Laravel, Python, Django, Node.js, RESTful APIs"
  }
  ```
* **Response** `201 Created`.

#### `PUT /api/skills/{id}`
* **Description**: Update skills list or category label.
* **Controller**: `SkillController@update`

#### `DELETE /api/skills/{id}`
* **Description**: Delete skill category.
* **Controller**: `SkillController@delete`

---

### 3.7 Education & Certifications (`/api/education` & `/api/certificates`)

#### `GET /api/education`
* **Controller**: `EducationController@getAll`

#### `POST /api/education`
* **Controller**: `EducationController@create`
* **Request Payload**:
  ```json
  {
    "school_name": "Western Mindanao State University",
    "course": "Bachelor of Science in Computer Science",
    "date_start": "2024-01-01",
    "date_end": "2028-06-01",
    "date_display": "2024 – 2028 (Expected)",
    "location": "Zamboanga City, Philippines",
    "focus_areas": "Software Design Patterns, Database Management..."
  }
  ```

#### `PUT /api/education/{id}` & `DELETE /api/education/{id}`
* **Controller**: `EducationController@update` & `EducationController@delete`

#### `GET /api/certificates`
* **Controller**: `CertificateController@getAll`

#### `POST /api/certificates`
* **Controller**: `CertificateController@create`
* **Request Payload**:
  ```json
  {
    "title": "Champion / 1st Place — Build With AI Hackathon",
    "issuer": "Build With AI Hackathon 2026",
    "date_display": "May 2026",
    "description": "Awarded 1st Place for architecting LimpioZambo...",
    "cert_id": "BWAI-2026-CHAMP",
    "cert_url": "https://github.com/Axxer001/BWAI-Hackathon-2026.git"
  }
  ```

#### `PUT /api/certificates/{id}` & `DELETE /api/certificates/{id}`
* **Controller**: `CertificateController@update` & `CertificateController@delete`

---

### 3.8 Contact Channels Directory (`/api/contact-info`)

#### `GET /api/contact-info`
* **Controller**: `ContactInfoController@getAll`

#### `POST /api/contact-info`
* **Controller**: `ContactInfoController@create`
* **Request Payload**:
  ```json
  {
    "contact_name": "GitHub",
    "contact_type": "url",
    "contact_info": "https://github.com/Nishieeee"
  }
  ```

#### `PUT /api/contact-info/{id}` & `DELETE /api/contact-info/{id}`
* **Controller**: `ContactInfoController@update` & `ContactInfoController@delete`

---

### 3.9 Contact Inquiries Inbox (`/api/inquiries`)

#### `POST /api/inquiries` *(Public Endpoint)*
* **Description**: Handles contact form submissions from the portfolio landing page.
* **Controller**: `ContactInquiryController@submit`
* **Request Payload**:
  ```json
  {
    "sender_name": "Sarah Connor",
    "sender_email": "sarah@cyberdyne.com",
    "subject": "Full-Stack Project Collaboration",
    "message": "Hi Clein, looking forward to discussing a new system project with you."
  }
  ```
* **Response** `201 Created`:
  ```json
  {
    "success": true,
    "message": "Inquiry submitted successfully."
  }
  ```

#### `GET /api/inquiries` *(Admin Endpoint)*
* **Description**: Retrieve list of received messages with timestamps and read/unread flags.
* **Controller**: `ContactInquiryController@getAll`

#### `PATCH /api/inquiries/{id}/read` *(Admin Endpoint)*
* **Description**: Mark inquiry as read (`is_read = 1`) or unread (`is_read = 0`).
* **Controller**: `ContactInquiryController@toggleReadStatus`

#### `DELETE /api/inquiries/{id}` *(Admin Endpoint)*
* **Description**: Remove an inquiry from the inbox.
* **Controller**: `ContactInquiryController@delete`
