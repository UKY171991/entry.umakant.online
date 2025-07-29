# Emails Table Structure Documentation

## ✅ Database Table: `emails`

This table supports the emails page functionality at `http://127.0.0.1:8000/emails`

### 📋 Table Columns

| Column Name | Data Type | Nullable | Description |
|-------------|-----------|----------|-------------|
| `id` | bigint (Primary Key) | No | Auto-incrementing unique identifier |
| `client_id` | bigint (Foreign Key) | Yes | References clients.id table |
| `client_name` | varchar(255) | Yes | Client name for display purposes |
| `email` | varchar(255) | Yes | Email address for communication |
| `phone` | varchar(255) | Yes | WhatsApp/Phone number |
| `email_template` | varchar(255) | Yes | Template type (website_proposal, project_update, etc.) |
| `project_name` | varchar(255) | Yes | Name of the project |
| `estimated_cost` | decimal(10,2) | Yes | Project cost estimate |
| `timeframe` | varchar(255) | Yes | Project timeline |
| `notes` | text | Yes | Additional notes and requirements |
| `last_email_sent_at` | timestamp | Yes | Last email sent timestamp |
| `last_whatsapp_sent_at` | timestamp | Yes | Last WhatsApp message sent timestamp |
| `created_at` | timestamp | No | Record creation timestamp |
| `updated_at` | timestamp | No | Record last update timestamp |

### 🔗 Relationships

- **Belongs To**: `Client` model via `client_id` foreign key
- **Foreign Key Constraint**: `client_id` references `clients.id` with cascade delete

### 📊 Fields Used on Emails Page

The emails page (`/emails`) displays and uses these fields:

#### **Table Display Columns:**
1. **#** - `id` (Serial number)
2. **CLIENT NAME** - `client_name` (from relationship or stored value)
3. **EMAIL** - `email` 
4. **PHONE** - `phone`
5. **PROJECT** - `project_name`
6. **TEMPLATE** - `email_template` (formatted for display)
7. **LAST CONTACT** - Latest of `last_email_sent_at` or `last_whatsapp_sent_at`
8. **ACTIONS** - View, Edit, Delete buttons
9. **SEND** - Email and WhatsApp send buttons

#### **Form Fields (Add/Edit Modal):**
- `client_name` - Client Name (required)
- `email` - Email Address (required if no phone)
- `phone` - WhatsApp Number (required if no email)
- `email_template` - Email Template (dropdown selection)
- `project_name` - Project Name
- `estimated_cost` - Estimated Cost (₹)
- `timeframe` - Timeframe
- `notes` - Additional Notes

#### **View Modal Fields:**
- `client_name` - Client Name
- `email` - Email Address
- `phone` - WhatsApp Number
- `email_template` - Template (formatted)
- `project_name` - Project Name
- `estimated_cost` - Estimated Cost (formatted with ₹)
- `timeframe` - Timeframe
- `notes` - Notes

### 🎯 Template Types Supported

The `email_template` field supports these values:
- `website_proposal` - 🚀 Website Development Proposal
- `project_update` - 📈 Project Status Update
- `project_completion` - 🎉 Project Completion
- `general_inquiry` - 📧 General Business Inquiry
- `follow_up` - 📞 Follow-up Email
- `pathology_management` - 🔬 Pathology Management System
- `hospital_management` - 🏥 Hospital Management System

### 💾 Model Configuration

**File**: `app/Models/Email.php`

```php
protected $fillable = [
    'client_id',
    'client_name',
    'email',
    'phone',
    'email_template',
    'project_name',
    'estimated_cost',
    'timeframe',
    'notes',
    'last_email_sent_at',
    'last_whatsapp_sent_at',
];

protected $casts = [
    'last_email_sent_at' => 'datetime',
    'last_whatsapp_sent_at' => 'datetime',
];
```

### 🔧 Migration History

The emails table was created and modified through these migrations:
1. `2025_07_01_121815_create_emails_table.php` - Initial table creation
2. `2025_07_02_075433_add_client_id_to_emails_table.php` - Added client_id foreign key
3. `2025_07_02_103535_remove_client_name_from_emails_table.php` - Removed client_name
4. `2025_07_04_085533_add_template_fields_to_emails_table.php` - Added template fields
5. `2025_07_07_043930_add_phone_to_emails_table.php` - Added phone field
6. `2025_07_07_045630_add_last_sent_fields_to_emails_table.php` - Added tracking fields
7. `2025_07_07_053652_update_emails_table_make_email_nullable.php` - Made email nullable
8. `2025_07_28_083410_update_emails_table_add_missing_fields.php` - Added client_name back

### ✅ Current Status

- ✅ Table exists and is properly structured
- ✅ All fields required by the emails page are present
- ✅ Foreign key relationships are established
- ✅ Model fillable fields match database columns
- ✅ Proper data types and constraints are set
- ✅ Supports all functionality shown on `/emails` page

The emails table is now fully configured to support all the functionality displayed on the emails page at `http://127.0.0.1:8000/emails`.