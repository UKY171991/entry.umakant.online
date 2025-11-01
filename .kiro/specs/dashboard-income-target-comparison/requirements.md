# Requirements Document

## Introduction

This feature extends the existing daily target income functionality by adding visual comparison displays on the dashboard page. Users will be able to see how their actual income compares to their target income on both monthly and yearly timeframes, providing valuable insights into their income performance and goal achievement.

## Glossary

- **Dashboard System**: The main dashboard page of the income management application
- **Income Comparison Widget**: Visual component displaying actual vs target income comparison
- **Target Income**: The daily target income amount set by the user (from existing daily-target-income feature)
- **Actual Income**: The sum of all recorded income entries for a specific time period
- **Monthly View**: Income comparison display for the current month
- **Yearly View**: Income comparison display for the current year
- **Performance Indicator**: Visual element showing whether actual income meets, exceeds, or falls short of target

## Requirements

### Requirement 1

**User Story:** As a user, I want to see a monthly comparison of my actual income versus my target income on the dashboard, so that I can track my monthly performance against my goals.

#### Acceptance Criteria

1. THE Dashboard System SHALL display a monthly income comparison widget on the main dashboard page
2. WHEN a user has set a daily target income, THE Dashboard System SHALL calculate the monthly target by multiplying the daily target by the number of days in the current month
3. THE Dashboard System SHALL calculate the actual monthly income by summing all income entries for the current month
4. THE Dashboard System SHALL display both actual and target amounts with clear visual distinction
5. THE Dashboard System SHALL show the percentage achievement of target income for the month

### Requirement 2

**User Story:** As a user, I want to see a yearly comparison of my actual income versus my target income on the dashboard, so that I can track my annual performance and long-term goal achievement.

#### Acceptance Criteria

1. THE Dashboard System SHALL display a yearly income comparison widget on the main dashboard page
2. WHEN a user has set a daily target income, THE Dashboard System SHALL calculate the yearly target by multiplying the daily target by the number of days in the current year
3. THE Dashboard System SHALL calculate the actual yearly income by summing all income entries for the current year
4. THE Dashboard System SHALL display both actual and target amounts with clear visual distinction
5. THE Dashboard System SHALL show the percentage achievement of target income for the year

### Requirement 3

**User Story:** As a user, I want visual indicators that clearly show whether I'm meeting, exceeding, or falling short of my income targets, so that I can quickly assess my performance at a glance.

#### Acceptance Criteria

1. THE Dashboard System SHALL use color coding to indicate performance status (green for meeting/exceeding target, yellow for close to target, red for significantly below target)
2. THE Dashboard System SHALL display progress bars or similar visual elements to show the ratio of actual to target income
3. THE Dashboard System SHALL show the absolute difference between actual and target income (surplus or deficit)
4. IF no target income is set, THEN THE Dashboard System SHALL display a message encouraging the user to set a target income
5. THE Dashboard System SHALL update the comparison data in real-time when new income entries are added

### Requirement 4

**User Story:** As a user, I want the income comparison widgets to be responsive and well-integrated with the existing dashboard layout, so that the information is easily accessible and doesn't disrupt my workflow.

#### Acceptance Criteria

1. THE Dashboard System SHALL integrate the comparison widgets seamlessly with the existing dashboard layout
2. THE Dashboard System SHALL ensure the widgets are responsive and display properly on different screen sizes
3. THE Dashboard System SHALL maintain consistent styling with the existing dashboard components
4. THE Dashboard System SHALL load the comparison data efficiently without significantly impacting page load times
5. THE Dashboard System SHALL handle cases where no income data exists for the selected time period gracefully