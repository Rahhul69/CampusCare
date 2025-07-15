College Maintenance Office Software
A web-based application to manage Lost and Found Items, Infrastructure Complaints, and Hall Booking within a college campus.

Table of Contents
  Project Overview
  Features
  Technologies Used
  Database Schema
  Installation
  Usage
  Contributors

Project Overview
This project is designed to streamline college maintenance by offering:
✅ A Lost and Found Management System
✅ A College Infrastructure Complaint System
✅ A Hall Booking System for events and practice sessions

It helps students, faculty, and administrators efficiently manage campus resources and maintenance requests.

Features
🏷 Lost and Found System
Students and staff can report lost or found items.
Users can claim lost items with verification.
Admin can update item status (Lost, Found, Returned).
🛠 Complaint Registration System
Users can register complaints related to college infrastructure (damaged furniture, faulty electricals, etc.).
Admin can assign and update complaint resolution status.
🏫 Hall Booking System
Students and faculty can book halls for events or practice.
Admin approves or rejects booking requests.
Users can view available and booked slots.

Technologies Used
Frontend:
HTML, CSS

Backend:
PHP (for logic and backend operations)
MySQL (for database management)

Database Schema
The database consists of the following key tables:
Users: Stores user details (students, faculty, admin).
LostAndFound: Manages lost and found items.
Complaints: Handles infrastructure-related complaints.
HallBooking: Stores hall booking requests and status.

Installation
Clone the repository:
bash
Copy
Edit
git clone https://github.com/Rahhhul69/college-maintenance-office.git
cd college-maintenance-office

Set up the database:
Import the provided database.sql file into MySQL.
Update database credentials in config.php.

Start the local server:
Use XAMPP or WAMP and place the project in htdocs folder.
Run the application on http://localhost/college-maintenance-office/.

Usage
User Registration/Login
Students, faculty, and admin log in with unique credentials.

Lost and Found
Users can report lost or found items.
Admin verifies and updates item status.

Complaint System
Users submit complaints with description and category.
Admin assigns and updates resolution status.

Hall Booking
Users request hall bookings with time slots.
Admin approves/rejects requests based on availability.

Contributors
👨‍💻 Rahhhul (Developer)
    Archana
     Prapti
