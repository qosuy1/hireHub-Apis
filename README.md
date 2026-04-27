# HireHub (MVP) API

[![View Postman Docs](https://img.shields.io/badge/Postman-API_Documentation-FF6C37?style=for-the-badge&logo=postman)](https://documenter.getpostman.com/view/45618842/2sBXqDuPjz)

The HireHub project is a platform designed to connect Freelancers with Clients holding Projects. The platform provides a full-featured RESTful API for managing users, skills, freelancer profiles, projects, offers, attachments, and features an advanced review system.

---

## 🚀 Getting Started

To run the project on your local environment, please follow these steps:

### Prerequisites:
- PHP ^8.2
- Composer
- Node.js & NPM
- Database Server (SQLite by default, or MySQL/PostgreSQL)
- **Redis** (required for caching and queue processing)

### Installation:
1. **Clone the repository:**
   ```bash
   git clone https://github.com/qosuy1/hireHub-Apis.git
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

3. **Configure Redis in your `.env`:**
   ```env
   CACHE_STORE=redis
   QUEUE_CONNECTION=redis

   REDIS_HOST=127.0.0.1
   REDIS_PASSWORD=null
   REDIS_PORT=6379
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

**Start the queue worker** (required for sending queued notifications and running background jobs):
```bash
php artisan queue:work --queue=notifications,default
```

---

## 📍 Endpoints List

📖 **[Click here to view the full, interactive Postman API Documentation](https://documenter.getpostman.com/view/45618842/2sBXqDuPjz)**

All system endpoints are prefixed with `/api`.

### 🔐 Authentication
- `POST /api/register` : Create a new user
- `POST /api/login` : Login user
- `POST /api/logout` : Logout user (Requires `auth:sanctum`)
- `GET /api/user` : Get current authenticated user details

### 🏠 Home & Dashboard
- `GET /api/v1/` : Home page data (paginated open projects, cached with Redis)
- `GET /api/v1/dashboard` : Admin dashboard (Requires `auth:sanctum` and `admin` middleware)
- `POST /api/v1/dashboard/verify-user/{user}` : Verify a user account (Requires `auth:sanctum` and `admin` middleware)

### 🛠 Skills
- `GET/POST/PUT/DELETE /api/v1/skills` : Manage core system skills (Requires `admin` for creation/modification/deletion)

### 👤 Freelancer Profiles
- `GET /api/v1/freelancer-profiles` : List freelancers (paginated, cached — defaults to `available` only, best-rated first)
- `POST/PUT/DELETE /api/v1/freelancer-profiles` : Manage freelancer profiles (Update & Delete require `verified_freelancer` middleware)
- `POST /api/v1/freelancer-profiles/{profile}/skills` : Add a skill to the freelancer profile
- `PUT /api/v1/freelancer-profiles/{profile}/skills/{skill}` : Update a freelancer's skill
- `DELETE /api/v1/freelancer-profiles/{profile}/skills/{skill}` : Remove a skill from the freelancer profile

**Freelancer listing query parameters:**

| Parameter | Default | Description |
|---|---|---|
| `available_now` | `true` | Show only `available` freelancers |
| `best_rated` | `true` | Sort by average rating descending |

### 💼 Projects
- `GET /api/v1/projects` : List projects (paginated, cached — defaults to `open` only)
- `POST/PUT/DELETE /api/v1/projects` : Manage full project lifecycle (Requires authentication for Create/Update/Delete)
- `POST /api/v1/projects/{project}/accept-offer/{offer}` : Accept a specific offer for the project
- `POST /api/v1/projects/{project}/review/project` : Leave a review on a completed project
- `POST /api/v1/projects/{project}/review/freelancer` : Leave a review on the freelancer who completed the project
- `GET /api/v1/projects/{project}/attachments` : Get project attachments
- `POST /api/v1/projects/{project}/attachments` : Upload a new project attachment
- `DELETE /api/v1/projects/{project}/attachments/{attachment}` : Delete a project attachment

**Projects listing query parameters:**

| Parameter | Default | Description |
|---|---|---|
| `all` | — | Pass `?all=1` to show all statuses (not just `open`) |
| `min_budget` | — | Show only projects with budget above this amount |
| `this_month` | — | Pass `?this_month=1` to show only projects created this month |

### 📝 Offers
- `POST/PUT/DELETE /api/v1/projects/{project}/offers` : Submit, update, or withdraw an offer for a specific project
- `GET /api/v1/offers/{offer}/attachments` : Get specific offer attachments
- `POST /api/v1/offers/{offer}/attachments` : Upload an attachment for an offer
- `DELETE /api/v1/offers/{offer}/attachments/{attachment}` : Remove an attachment from an offer

### ⭐ Reviews
- `PUT /api/v1/reviews/{review}` : Update an existing review (owner only)
- `DELETE /api/v1/reviews/{review}` : Delete an existing review (owner only)

---

## 🏛 Important Architectural Decisions

During the development of the system, several decisions and design patterns were implemented to ensure scalability, cleanliness, and ease of maintenance:

1. **Service Pattern:**
   The `app/Services` namespace was introduced to decouple complex business logic from the HTTP Controllers. This decision ensures controllers solely handle request data, route it to the appropriate service, and return responses. (e.g., `FreelancersService.php`, `ReviewService.php`, `ProjectService.php`).

2. **API Versioning:**
   Controllers, Services, and Resources are structured within `v1` directories. This ensures backward compatibility for mobile clients or external consumers if breaking changes occur in future versions.

3. **Laravel Built-in Notification System:**
   The project uses **Laravel's native notification system** (`app/Notifications`) replacing any custom mailer/notifier infrastructure. All notifications implement `ShouldQueue` for non-blocking delivery:

   | Notification | Trigger |
   |---|---|
   | `ProjectCreatedNotification` | Client creates a new project |
   | `NewOfferNotification` | A freelancer submits an offer |
   | `OfferAcceptedNotification` | The client accepts an offer |
   | `OfferRejectedNotification` | An offer is rejected when another is accepted |
   | `NewReviewNotification` | A client leaves a review for a freelancer |

4. **Queue & Background Jobs (`app/Jobs`):**
   Heavy or time-sensitive tasks are offloaded to the queue using **Redis** as the queue driver. Jobs run asynchronously so API responses are never blocked:

   | Job | Purpose |
   |---|---|
   | `RejectOffers` | Marks all other offers as rejected and notifies their owners after an offer is accepted |
   | `UpdateFreelancerProfileRating` | Recalculates and persists a freelancer's `average_rating` when a review is created, updated, or deleted. Uses `ShouldBeUnique` + `lockForUpdate` to prevent race conditions |

   Notifications to rejected freelancers are dispatched inside `DB::afterCommit()` to guarantee the database transaction has fully committed before the queue picks up the jobs.

5. **Redis Caching:**
   Frequently accessed, expensive list queries are cached using **Redis** with tag-based invalidation (`Cache::tags()`):

   | Cached Data | Cache Tag | Invalidated When |
   |---|---|---|
   | Paginated open projects list (`GET /projects`) | `projects` | Project is created, updated, or deleted |
   | Paginated home projects feed | `projects` | Project is created, updated, or deleted |
   | Paginated freelancers list (`GET /freelancer-profiles`) | `freelancers` | Freelancer profile is created, updated, or deleted |

   Cache keys include the page number and active filters, ensuring each unique combination is cached independently.

6. **API Resources (Transformers):**
   `app/Http/Resources/v1` acts as a data transformation layer. This ensures consistent JSON response structures across all endpoints, efficiently hiding sensitive fields and cleanly formatting relationships, such as detailed attachments.

7. **Middlewares & Form Requests:**
   - **Form Requests** are used extensively to centralize and standardize inbound data validation.
   - **Role-based Middlewares** ensure secure access dynamically. For example, `admin` middleware locks out regular users from critical routes, while `verified_freelancer` guards actions strictly meant for documented professionals.

8. **Sanctum Authentication:**
   `Laravel Sanctum` was chosen as the authentication driver to provide lightweight and secure APIs over classic JWT implementations. It's ideally suited for SPA applications and mobile consumers, seamlessly providing revokable token-based protection.

9. **Performance Optimization:**
   - **Eager Loading** (`with()`) is used throughout to prevent N+1 query issues on nested relationships.
   - **Paginated responses** include full `links` and `meta` pagination metadata via `ApiResponse::paginated()`.
   - **Average ratings** are maintained in a denormalized `average_rating` column on `freelancer_profiles`, updated asynchronously via the `UpdateFreelancerProfileRating` job to keep reads fast.
