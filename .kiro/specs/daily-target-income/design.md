# Design Document - Daily Target Income Feature

## Overview

This feature adds a daily target income setting that automatically populates the total amount field when creating new income entries. The design integrates seamlessly with the existing Laravel-based income management system, extending the User model to store target income preferences and modifying the income creation workflow to utilize this setting.

## Architecture

### Database Schema Changes

**Users Table Extension:**
- Add `daily_target_income` column (decimal, nullable) to store the user's target income amount
- Migration will be backward compatible with existing user records

**No changes required to existing Income table structure**

### Component Integration

The feature integrates with existing components:
- **User Model**: Extended to include target income field
- **IncomeController**: Modified to provide target income data to views
- **Income Views**: Enhanced to auto-fill total amount field
- **User Settings**: New interface for managing target income

## Components and Interfaces

### 1. Database Layer

**User Model Extension:**
```php
// Add to User model fillable array
protected $fillable = [
    'name',
    'email', 
    'password',
    'daily_target_income'  // New field
];

// Add accessor for formatted target income
public function getFormattedTargetIncomeAttribute()
{
    return $this->daily_target_income ? number_format($this->daily_target_income, 2) : null;
}
```

**Migration:**
- Create migration to add `daily_target_income` column to users table
- Column type: `decimal(10,2)` nullable with default null

### 2. Controller Layer

**IncomeController Modifications:**
- Modify `index()` method to pass user's target income to view
- Modify `create()` method to include target income in response

**New UserSettingsController:**
- Handle target income CRUD operations
- Validate target income input (positive numeric values)
- Provide API endpoints for AJAX updates

### 3. View Layer

**Income Form Enhancement:**
- Auto-populate `total_amount` field with target income on form load
- Maintain existing form validation and calculation logic
- Add visual indicator when target income is auto-filled

**Settings Interface:**
- New settings section for target income management
- Input field with validation for target income amount
- Save/update functionality with user feedback

### 4. Frontend JavaScript

**Income Form Auto-fill Logic:**
```javascript
// Auto-fill total amount with target income when creating new income
$('#createNewIncome').click(function () {
    // Existing logic...
    
    // Auto-fill target income if available
    if (window.userTargetIncome && window.userTargetIncome > 0) {
        $('#total_amount').val(window.userTargetIncome);
        // Trigger calculation for pending/received amounts
        $('#total_amount').trigger('input');
    }
});
```

**Settings Form Handling:**
- AJAX form submission for target income updates
- Real-time validation feedback
- Success/error message handling

## Data Models

### User Model Extension

```php
class User extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'daily_target_income'
    ];
    
    protected $casts = [
        'daily_target_income' => 'decimal:2'
    ];
    
    // Accessor for formatted display
    public function getFormattedTargetIncomeAttribute()
    {
        return $this->daily_target_income ? 
            number_format($this->daily_target_income, 2) : '0.00';
    }
}
```

### Settings Data Transfer

```php
// Request validation rules
$rules = [
    'daily_target_income' => 'nullable|numeric|min:0|max:999999.99'
];

// Response format
{
    "success": true,
    "message": "Target income updated successfully",
    "target_income": "5000.00"
}
```

## Error Handling

### Validation Errors
- **Invalid Amount**: Display error for non-numeric or negative values
- **Amount Too Large**: Limit target income to reasonable maximum (999,999.99)
- **Database Errors**: Handle connection issues gracefully with user-friendly messages

### Fallback Behavior
- If target income is not set, form behaves as current implementation
- If target income retrieval fails, log error but don't block form functionality
- Auto-fill failure should not prevent manual entry

### User Feedback
- Success messages for target income updates
- Clear error messages for validation failures
- Loading states during AJAX operations

## Testing Strategy

### Unit Tests
- User model target income accessor/mutator methods
- Target income validation rules
- Controller method responses

### Integration Tests
- Target income setting and retrieval workflow
- Income form auto-fill functionality
- Settings update API endpoints

### Frontend Tests
- Auto-fill JavaScript functionality
- Form validation behavior
- AJAX request handling

### User Acceptance Tests
- Complete workflow: set target income → create income entry → verify auto-fill
- Settings management: update target income → verify persistence
- Edge cases: no target income set, invalid values, form reset behavior

## Implementation Considerations

### Performance
- Target income is loaded once per session, minimal database impact
- No additional queries during income listing/filtering operations
- Settings updates are infrequent user actions

### Security
- Target income is user-specific data, no cross-user access concerns
- Standard Laravel validation and CSRF protection applies
- No sensitive data exposure in client-side JavaScript

### Backward Compatibility
- New database column is nullable, existing users unaffected
- Existing income creation workflow remains unchanged when no target is set
- No breaking changes to existing API endpoints

### User Experience
- Auto-fill provides convenience without forcing usage
- Users can override auto-filled values as needed
- Clear visual feedback for auto-filled vs manually entered values
- Settings are easily accessible and intuitive to use

## Future Enhancements

### Potential Extensions
- Multiple target income profiles (weekday/weekend, different clients)
- Target income history and analytics
- Smart suggestions based on historical income patterns
- Integration with expense targets for net income goals

### Scalability Considerations
- Current design supports single target per user
- Database schema allows for future expansion to multiple targets
- Settings interface can be extended for additional preferences