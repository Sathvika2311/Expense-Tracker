# Expense Tracker - Project Documentation

## 1. Cover Page
- Name: RUTAGANIRA SHEMA Derrick
- Registration Number: 26506
- Course: Web Design
- Project Title: Personal Finance Manager - Expense Tracker System

## 2. Table of Contents
1. Project Introduction
2. Problem Statement
3. System Requirements
4. System Design
5. Implementation
6. Database Design
7. Testing
8. Challenges Faced
9. Conclusion
10. Screenshots

## 3. Project Introduction
- **Title:** Personal Finance Manager - Expense Tracker System
- **Case Study:** Individuals and families needing to manage personal finances effectively
- **Purpose:** To help users track expenses, income, set budgets, and achieve savings goals
- **Technologies Used:** HTML5, CSS3, JavaScript, PHP, MySQL, Chart.js

The Personal Finance Manager is a comprehensive web application designed to help users take control of their financial lives. It provides an intuitive interface for tracking expenses and income, visualizing spending patterns, setting and monitoring budgets, and working towards savings goals.

## 4. Problem Statement
### Problem
Many individuals struggle with managing their personal finances effectively. They lack tools to:
- Track where their money goes
- Plan and stick to budgets
- Save systematically for future goals
- Gain insights into their spending patterns

### Users
- Students managing limited funds
- Young professionals learning financial responsibility
- Families budgeting for household expenses
- Anyone seeking to improve their financial health

### Features That Help Users
- Expense and income tracking with categorization
- Budget setting and monitoring by category
- Savings goals with progress tracking
- Visual reports and analytics
- Data export functionality
- Mobile-responsive design for access anywhere

## 5. System Requirements
### Software Requirements
- Web Browser: Chrome, Firefox, Safari, Edge (latest versions)
- Server Environment: XAMPP/WAMP with PHP 7.4+ and MySQL 5.7+
- Development Tools: VS Code, Git for version control
- Dependencies: Chart.js for data visualization

### Hardware Requirements (Recommended)
- Any device capable of running a modern web browser
- Internet connection for accessing the application

## 6. System Design
### User Flow
The application follows a logical user flow as illustrated in the flowchart (see diagrams/flowchart.md):
1. User registers/logs in
2. Lands on dashboard with overview
3. Can add expenses or income
4. Can set budgets by category
5. Can create savings goals
6. Can view detailed reports and analytics
7. Can export financial data

### Pages
- **Home/Landing Page:** Introduction to the application
- **Register/Login:** User authentication
- **Dashboard:** Overview of financial status
- **Add Expense:** Record new expenses with categories
- **Add Income:** Record income from various sources
- **Budget Manager:** Set and track category budgets
- **Savings Goals:** Create and monitor savings targets
- **Reports:** Detailed financial analytics and charts
- **Documentation:** User guide and help

### Navigation
The application uses an intuitive navigation system with:
- Clear menu items in the dashboard
- Consistent back buttons
- Logical progression between related pages
- Mobile-friendly navigation for smaller screens

## 7. Implementation
### Frontend Development
- **HTML5:** Semantic markup for structure
- **CSS3:**
  - Custom styling with gradients and animations
  - Responsive design for all screen sizes
  - Consistent color scheme and typography
  - CSS animations for enhanced user experience
- **JavaScript:**
  - Form validation for data integrity
  - Interactive charts using Chart.js
  - Dynamic content updates
  - Export functionality for reports

### Backend Development
- **PHP:**
  - User authentication and session management
  - Form processing and data validation
  - Database operations (CRUD)
  - Data aggregation for reports
- **MySQL:**
  - Relational database for storing user data
  - Optimized queries for performance
  - Data integrity through foreign keys

### Features Implemented
1. **User Authentication System**
   - Registration with email verification
   - Secure login with password hashing
   - Session management

2. **Expense & Income Tracking**
   - Categorized expense recording
   - Income source tracking
   - Date-based organization

3. **Budget Management**
   - Monthly budget setting by category
   - Visual progress tracking
   - Alerts for over-budget categories

4. **Savings Goals**
   - Target amount setting
   - Progress tracking
   - Target date monitoring

5. **Reports & Analytics**
   - Visual charts for expense breakdown
   - Income vs. expense trends
   - Category-based analysis
   - Data export functionality

6. **Responsive Design**
   - Mobile-first approach
   - Adapts to all screen sizes
   - Touch-friendly interface

## 8. Database Design
### Database Schema
The database consists of six main tables (see diagrams/er_diagram.md):
1. **users** - Stores user account information
2. **expenses** - Records all user expenses
3. **income** - Tracks all user income sources
4. **categories** - Predefined expense categories
5. **budgets** - User-defined budget limits by category
6. **savings_goals** - User savings targets and progress

### Relationships
- One user can have many expenses (one-to-many)
- One user can have many income records (one-to-many)
- One user can set many budgets (one-to-many)
- One user can have many savings goals (one-to-many)

### Data Types
- Appropriate data types chosen for each field
- DECIMAL for monetary values for precision
- VARCHAR for text with appropriate length limits
- DATE and TIMESTAMP for temporal data
- Foreign keys for referential integrity

## 9. Testing
### Functionality Testing
- Form submissions tested with valid and invalid data
- CRUD operations verified for all entities
- Authentication system tested for security
- Budget calculations checked for accuracy
- Reports verified against raw data

### Compatibility Testing
- Tested on multiple browsers (Chrome, Firefox, Safari, Edge)
- Verified on different devices (desktop, tablet, mobile)
- Checked with various screen resolutions

### User Experience Testing
- Navigation flow tested for intuitiveness
- Form feedback verified for clarity
- Visual elements checked for consistency
- Performance tested for responsiveness

### Issues Found and Fixed
- Fixed calculation errors in budget percentage display
- Resolved mobile layout issues on small screens
- Corrected date formatting inconsistencies
- Optimized database queries for better performance
- Enhanced form validation for better user feedback

## 10. Challenges Faced
### Technical Challenges
- **Challenge:** Implementing real-time chart updates
  **Solution:** Used AJAX to fetch data and Chart.js for rendering

- **Challenge:** Ensuring accurate budget calculations
  **Solution:** Implemented server-side validation and double-checking of calculations

- **Challenge:** Responsive design for data-heavy pages
  **Solution:** Used CSS Grid and Flexbox with media queries for adaptive layouts

### Design Challenges
- **Challenge:** Creating an intuitive interface for complex financial data
  **Solution:** Used visual cues, color coding, and progressive disclosure

- **Challenge:** Balancing feature richness with simplicity
  **Solution:** Implemented a clean dashboard with drill-down capabilities

## 11. Conclusion
### Achievements
- Successfully created a comprehensive financial management system
- Implemented all core features with a well designed user interface
- Ensured mobile responsiveness and cross-browser compatibility
- Provided valuable data visualization and insights

### Lessons Learned
- The importance of user-centered design in financial applications
- Techniques for effective data visualization
- Best practices for secure user authentication
- Strategies for responsive web design

### Future Improvements
- Integration with bank accounts for automatic transaction import
- Recurring transaction scheduling
- Bill payment reminders
- Financial goal recommendations based on spending patterns
- Multi-currency support
- Mobile app development

## 12. Screenshots
*[Include screenshots of key pages and features here]*

---
*This documentation provides a comprehensive overview of the Personal Finance Manager - Expense Tracker System, detailing its purpose, design, implementation, and testing process.* 