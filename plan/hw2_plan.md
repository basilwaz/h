---
marp: true
size: 4:3
---

# Project 1 Plan

**Submission Deadline:** October 23

Planning anchors: HW2 is due in Week 4, Project 1 starts in Week 5, HW3 Q&A
is in Week 7, Midterm 1 is in Week 8, and Project 1 is due in Week 9.

---

## Your Project 1 Submission Date

1. **When (Plan):** October 21 
2. **When (Actual):** October 23
3. **Comment:** I plan to finish two days early so I have time to test everything and fix any problems.

---

## Planned API Endpoints

List the 10 REST API endpoints you plan to implement for Project 1. Mark
Bearer-token-protected endpoints with `*` (at least 2 required). Leave
"Implemented On" blank for now — in Project 1 you will fill in the actual
date you finished each endpoint, as proof that your implementation followed
your plan.

Example format:

```txt
* means Bearer token required
GET /students → Get all students
GET /students/1 → Get student by ID
POST /students → Create new student
PUT /students/1 → Update student
DELETE /students/1 → Delete student *
```
My project is a simple movie collection API. The earlier plan used genres. I changed the topic to movies and genres. The project still has the same CRUD structure and two protected delete routes.

| # | Endpoint | Description | Bearer | Implemented On |
|---|----------|-------------|:---:|---|
| 1 | `GET /movies` | Get all movies |  | October 23, 2026 |
| 2 | `GET /movies/1` | Get one movie by ID |  | October 23, 2026 |
| 3 | `POST /movies` | Add a new movie |  | October 23, 2026 |
| 4 | `PUT /movies/1` | Update an movie |  | October 23, 2026 |
| 5 | `DELETE /movies/1` | Delete an movie | * | October 23, 2026 |
| 6 | `GET /genres` | Get all genres |  | October 23, 2026 |
| 7 | `GET /genres/1` | Get one genre by ID |  | October 23, 2026 |
| 8 | `POST /genres` | Add a new genre |  | October 23, 2026 |
| 9 | `PUT /genres/1` | Update a genre |  | October 23, 2026 |
| 10 | `DELETE /genres/1` | Delete a genre | * | October 23, 2026 |

---

## Project 1 Milestones

### Milestone 1: API and Database Design

1. **What:** Finalize all 10 endpoints planned in HW2 (2 of them Bearer-token protected); design the MySQL table(s).
2. **When (Plan):**  September 28
3. **When (Actual):** September 30
4. **Comment:** I will plan the tables and endpoints before I start writing the full API.

---

### Milestone 2: PHP/MySQL Implementation and Tests

1. **What:** Implement CRUD endpoints and create cURL plus HTML/JavaScript tests.
2. **When (Plan):** October 12
3. **When (Actual):** October 12
4. **Comment:** I will test each endpoint as I finish it so problems are easier to find.

---

### Milestone 3: Tutorial and NGINX Deployment

1. **What:** Complete tutorial slides, test NGINX deployment, and organize `code/`, `presentation/`, and `plan/`.
2. **When (Plan):**  October 21
3. **When (Actual):** October 23
4. **Comment:** I will finish the deployment and slides before the October 23 deadline.

---
