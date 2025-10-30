# Smart Community Portal (SCP)

**Capstone Project – Web-Based Application Development & Team Leadership**  
**Client:** *CityLink Initiatives* (fictional local government agency)  
**Project Duration:** 14 weeks (3 hrs/week) – 4 sprints

---

## 📖 Project Overview

CityLink Initiatives is a publicly funded department delivering essential community services such as:

- Event bookings
- Waste management information
- Rates enquiries
- Community development programs

Currently, their services rely on manual processes and outdated web pages.  
The **Smart Community Portal (SCP)** aims to modernise this experience by providing a **dynamic, accessible, and mobile-friendly** web platform that centralises services, announcements, bookings, feedback, and user profiles.

This project will be delivered by a self-managed student development team, rotating leadership roles, and focusing on both **technical delivery** and **quality outcomes**.

---

## 🎯 Project Goals

- Build a professional, usable prototype of the SCP.
- Improve efficiency, accessibility, and citizen engagement.
- Ensure compliance with **Australian Privacy Principles (APPs)** and **WCAG 2.1 accessibility standards**.
- Deliver version-controlled code and clear documentation.
- Provide training and a smooth handover for future maintenance.

---

## 🛠 Features

- **Announcements** – Post and manage community updates.
- **Service Bookings** – Allow residents to book council services/events.
- **Feedback Forms** – Collect and review public feedback securely.
- **User Profiles** – Manage personal details and service history.
- **XML-based Configurations** – Menu and content updates for scalability.

---

## 📋 Policies & Procedures

### 1. Change Management
- All changes approved by the *Digital Transformation Officer* (lecturer).
- Formal changeover plan with timelines, data migration, and contingencies.
- No disruption to existing services during testing/deployment.

### 2. Data Security & Privacy
- Handle all data in line with the **Australian Privacy Principles**.
- Use only mock/sample data during development.
- Store data securely and prevent unauthorised access.

### 3. Compatibility & Accessibility
- WCAG 2.1 compliant for accessibility.
- Mobile-friendly and compatible with all major browsers.
- XML configurations for scalable updates.

### 4. Communication & Reporting
- Weekly updates to the client via email/agreed channel.
- End-of-sprint reviews with progress documentation.
- Immediate escalation of major risks/blockers.

### 5. Training & Handover
- Provide user manuals and training docs.
- Final handover session covering:
  - Content updates (XML)
  - User & feedback management
  - System checks and backups

---

## 📅 Timeline

| Week | Activity |
|------|----------|
| 1-3  | Sprint 1 – Requirements, planning, initial prototypes |
| 4-6  | Sprint 2 – Core functionality implementation |
| 7-10 | Sprint 3 – Accessibility, mobile responsiveness, feedback integration |
| 11-14| Sprint 4 – Testing, refinements, documentation |
| 16   | Training & handover |
| 18   | Final prototype delivery |

---

## 👥 Team Expectations

- Demonstrate **leadership** and **agile collaboration**.
- Use **user-centred design** principles.
- Ensure transparency and digital inclusion.
- Maintain a version-controlled repository.

---

## 📦 Deliverables

- Functional SCP prototype
- Version-controlled source code
- User manuals & training documentation
- Sprint review presentations
- Handover session

---

## 💾 SQL Queries

### Announcements Table
```sql
CREATE TABLE announcements (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  priority    ENUM('high','medium','low') NOT NULL DEFAULT 'medium',
  title       VARCHAR(200) NOT NULL,
  body        TEXT NOT NULL,
  `start`     DATE NULL,
  `end`       DATE NULL,
  category    VARCHAR(100) NOT NULL DEFAULT 'General',
  link_url    VARCHAR(500) NULL,
  link_text   VARCHAR(200) NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_dates (`start`, `end`)
);
```

### Users Table
```sql
CREATE TABLE `citylink`.`users` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(75) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `role` VARCHAR(10) NOT NULL DEFAULT 'user',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
```

### Feedback Table
```sql
CREATE TABLE feedback (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  subject VARCHAR(255),
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
```

### Reservations Table
```sql
CREATE TABLE reservations (
  reservation_id  INT AUTO_INCREMENT PRIMARY KEY,
  event_id        VARCHAR(100) NOT NULL,
  name            VARCHAR(100) NOT NULL,
  email           VARCHAR(254) NOT NULL,
  amount          INT NOT NULL CHECK (amount >= 1),
  created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
```

### Events Table
```sql
CREATE TABLE events (
    id VARCHAR(50) PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    date_info VARCHAR(50),
    location VARCHAR(255),
    cta_label VARCHAR(100)
)
```
---

## 📜 License

This project is for educational purposes only.  
No real personal data will be collected during development.


---

**End of Document**
