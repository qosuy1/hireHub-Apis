# HireHub (MVP) API

The HireHub project is a platform designed to connect Freelancers with Clients holding Projects. The platform provides a full-featured RESTful API for managing users, skills, freelancer profiles, projects, offers, attachments, and features an advanced review system.

---

## 🚀 Getting Started

To run the project on your local environment, please follow these steps:

### Prerequisites:
- PHP ^8.2
- Composer
- Node.js & NPM
- Database Server (SQLite by default, or MySQL/PostgreSQL)

### Installation:
1. **Clone the repository:**
   ```bash
   git clone <repository_url>
   cd hireHub
   ```

2. **Install dependencies and setup environment:**
   The project includes a custom `setup` script in `composer.json` that you can run to do everything at once:
   ```bash
   composer setup
   ```
   **Alternatively, you can run the steps manually:**
   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate
   npm install
   npm run build
   ```

### 💾 Seeding Dummy Data (Optional)
To test the features of the application, you can populate your database with dummy data including countries, cities, initial skills, tags, users, projects, and offers. Run the following artisan command:
```bash
php artisan db:seed
```
*Note: Make sure you have run the migrations before attempting to seed the database.*

### 🖥️ Running the Server:
You can use the built-in development script that runs everything concurrently (server, queue, Vite, etc.):
```bash
composer dev
```
Or simply use the traditional Laravel serve command:
```bash
php artisan serve
```

---

## 📍 Endpoints List

All system endpoints are prefixed with `/api`.

### 🔐 Authentication
- `POST /api/register` : Create a new user
- `POST /api/login` : Login user
- `POST /api/logout` : Logout user (Requires `auth:sanctum`)
- `GET /api/user` : Get current authenticated user details

### 🏠 Home & Dashboard
- `GET /api/v1/` : Home page data
- `GET /api/v1/dashboard` : Admin dashboard (Requires `auth:sanctum` and `admin` middleware)

### 🛠 Skills
- `GET/POST/PUT/DELETE /api/v1/skills` : Manage core system skills (Requires `admin` for creation/modification/deletion)

### 👤 Freelancer Profiles
- `GET/POST/PUT/DELETE /api/v1/freelancer-profiles` : Manage freelancer profiles (Update & Delete require `verified_freelancer` middleware).
- `POST /api/v1/freelancer-profiles/{profile}/skills` : Add a skill to the freelancer profile.
- `PUT /api/v1/freelancer-profiles/{profile}/skills/{skill}` : Update a freelancer's skill.
- `DELETE /api/v1/freelancer-profiles/{profile}/skills/{skill}` : Remove a skill from the freelancer profile.

### 💼 Projects
- `GET/POST/PUT/DELETE /api/v1/projects` : Manage full project lifecycle (Requires authentication for Create/Update/Delete).
- `POST /api/v1/projects/{project}/review` : Add a review to a completed project.
- `GET /api/v1/projects/{project}/attachments` : Get project attachments.
- `POST /api/v1/projects/{project}/attachments` : Upload a new project attachment.
- `DELETE /api/v1/projects/{project}/attachments/{attachment}` : Delete a project attachment.

### 📝 Offers
- `POST/PUT/DELETE /api/v1/projects/{project}/offers` : Submit, update, or withdraw an offer for a specific project.
- `POST /api/v1/projects/{project}/accept-offer/{offer}` : Accept a specific offer for the project.
- `GET /api/v1/offers/{offer}/attachments` : Get specific offer attachments.
- `POST /api/v1/offers/{offer}/attachments` : Upload an attachment for an offer.
- `DELETE /api/v1/offers/{offer}/attachments/{attachment}` : Remove an attachment from an offer.

---

## 🏛 Important Architectural Decisions

During the development of the system, several decisions and design patterns were implemented to ensure scalability, cleanliness, and ease of maintenance:

1. **Service Pattern:**
   The `app/Services` namespace was introduced to decouple complex business logic from the HTTP Controllers. This decision ensures controllers solely handle request data, route it to the appropriate service, and return responses. (e.g., `FreelancersService.php`, `ReviewService.php`).

2. **API Versioning:**
   Controllers, Services, and Resources are structured within `v1` directories. This ensures backward compatibility for mobile clients or external consumers if breaking changes occur in future versions.

3. **Observers Pattern:**
   Laravel Observers (located in `app/Observers`, like `ReviewObserver`) are used to execute background tasks natively when model events fire. For example, automatically recalculating a freelancer's average rating when a new review is submitted, keeping the primary controllers clean.

4. **API Resources (Transformers):**
   `app/Http/Resources/v1` acts as a data transformation layer. This ensures consistent JSON response structures across all endpoints, efficiently hiding sensitive fields and cleanly formatting relationships, such as detailed attachments.

5. **Middlewares & Form Requests:**
   - **Form Requests** are used extensively to centralize and standardize inbound data validation.
   - **Role-based Middlewares** ensure secure access dynamically. For example, `admin` middleware locks out regular users from critical routes, while `verified_freelancer` guards actions strictly meant for documented professionals.

6. **Sanctum Authentication:**
   `Laravel Sanctum` was chosen as the authentication driver to provide lightweight and secure APIs over classic JWT implementations. It's ideally suited for SPA applications and mobile consumers, seamlessly providing revokable token-based protection.

7. **Performance Optimization:**
   The application handles database interactions efficiently. Complex nested relationships (such as Freelancer `skills`, Project `offers`, and various `attachments`) are retrieved using Eager Loading (`with()`) wherever possible to strictly prevent the N+1 query performance bottleneck. Furthermore, heavy processing such as aggregations (e.g., updating average ratings) are offloaded to Observers, keeping API response times fast and lean.
