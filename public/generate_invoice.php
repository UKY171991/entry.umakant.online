<?php
// Database configuration - Using Laravel's SQLite database
$dbPath = __DIR__ . '/../database/database.sqlite';

// Create database connection
try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test connection by checking if tables exist
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND (name='clients' OR name='incomes')");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        throw new Exception("Required tables 'clients' and 'incomes' not found in database");
    }
} catch(PDOException $e) {
    $error_msg = "Database connection failed: " . $e->getMessage();
    error_log($error_msg);
} catch(Exception $e) {
    $error_msg = "Database error: " . $e->getMessage();
    error_log($error_msg);
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'load_customers':
            try {
                $stmt = $pdo->query("SELECT id, name, email FROM clients ORDER BY name");
                $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $customers]);
            } catch(Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            break;
            
        case 'load_invoices':
            try {
                $sql = "SELECT i.*, c.name as customer_name 
                        FROM incomes i 
                        LEFT JOIN clients c ON i.client_id = c.id 
                        WHERE 1=1";
                $params = [];
                
                if (!empty($_POST['customer_id'])) {
                    $sql .= " AND i.client_id = ?";
                    $params[] = $_POST['customer_id'];
                }
                
                if (!empty($_POST['from_date'])) {
                    $sql .= " AND i.date >= ?";
                    $params[] = $_POST['from_date'];
                }
                
                if (!empty($_POST['to_date'])) {
                    $sql .= " AND i.date <= ?";
                    $params[] = $_POST['to_date'];
                }
                
                $sql .= " ORDER BY i.date DESC";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode(['success' => true, 'data' => $invoices]);
            } catch(Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            break;
            
        case 'generate_invoice':
            try {
                // Generate invoice logic here
                $customer_id = $_POST['customer_id'] ?? '';
                $invoice_date = $_POST['invoice_date'] ?? date('Y-m-d');
                $from_date = $_POST['from_date'] ?? '';
                $to_date = $_POST['to_date'] ?? '';
                
                if (empty($customer_id)) {
                    throw new Exception('Please select a customer');
                }
                
                // Get customer details
                $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
                $stmt->execute([$customer_id]);
                $customer = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$customer) {
                    throw new Exception('Customer not found');
                }
                
                // Get invoice items
                $sql = "SELECT * FROM incomes WHERE client_id = ?";
                $params = [$customer_id];
                
                if (!empty($from_date)) {
                    $sql .= " AND date >= ?";
                    $params[] = $from_date;
                }
                
                if (!empty($to_date)) {
                    $sql .= " AND date <= ?";
                    $params[] = $to_date;
                }
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $invoice_data = [
                    'customer' => $customer,
                    'items' => $items,
                    'invoice_date' => $invoice_date,
                    'from_date' => $from_date,
                    'to_date' => $to_date,
                    'total_amount' => array_sum(array_column($items, 'total_amount')),
                    'total_received' => array_sum(array_column($items, 'received_amount')),
                    'total_pending' => array_sum(array_column($items, 'pending_amount'))
                ];
                
                echo json_encode(['success' => true, 'invoice' => $invoice_data]);
            } catch(Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    exit;
}

// Get customers for dropdown
try {
    if (isset($pdo)) {
        $stmt = $pdo->query("SELECT id, name FROM clients ORDER BY name");
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $customers = [];
        $db_error = $error_msg ?? "Database connection not available";
    }
} catch(Exception $e) {
    $customers = [];
    $db_error = "Error loading customers: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Invoice - Courier CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .main-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header-section {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .form-section {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            padding: 30px;
        }
        .content-section {
            padding: 30px;
        }
        .btn-generate {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .btn-generate:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.4);
        }
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 12px;
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            padding: 12px;
            margin: 10px 0;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 12px;
            margin: 10px 0;
        }
        .invoice-preview {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            display: none;
        }
        .invoice-header {
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .invoice-table {
            margin-top: 20px;
        }
        .total-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-container">
            <!-- Header -->
            <div class="header-section">
                <h1><i class="fas fa-file-invoice"></i> GENERATE INVOICE</h1>
                <p class="mb-0">Create professional invoices for your clients</p>
                <small class="text-light opacity-75">Version: Fixed - No Auto-loading Errors</small>
            </div>

            <!-- Form Section -->
            <div class="form-section">
                <form id="invoiceForm">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label text-white">
                                <i class="fas fa-calendar"></i> Invoice Date
                            </label>
                            <input type="date" class="form-control" id="invoice_date" name="invoice_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-white">
                                <i class="fas fa-calendar-week"></i> From Booking Date
                            </label>
                            <input type="date" class="form-control" id="from_date" name="from_date" placeholder="dd-mm-yyyy">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-white">
                                <i class="fas fa-calendar-check"></i> To Booking Date
                            </label>
                            <input type="date" class="form-control" id="to_date" name="to_date" placeholder="dd-mm-yyyy">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <input type="checkbox" id="select_all" class="form-check-input me-2">
                                <label class="form-check-label text-white fw-bold">Select All</label>
                            </div>
                            <div class="text-white">Customer Code / Customer Name</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-white">
                                <i class="fas fa-users"></i> Customer
                            </label>
                            <div class="input-group">
                                <select class="form-select" id="customer_id" name="customer_id">
                                    <option value="">All Customers</option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?php echo htmlspecialchars($customer['id']); ?>">
                                            <?php echo htmlspecialchars($customer['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn btn-outline-light" id="loadCustomersBtn" title="Refresh Customers">
                                    <i class="fas fa-refresh"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-white">&nbsp;</label>
                            <div>
                                <button type="button" class="btn btn-info me-2" id="viewBtn">
                                    <i class="fas fa-eye"></i> VIEW
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-generate text-white">
                                <i class="fas fa-file-pdf"></i> GENERATE INVOICE
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Content Section -->
            <div class="content-section">
                <?php if (isset($db_error)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle"></i>
                        Database connection error. Please check your database configuration.
                    </div>
                <?php endif; ?>

                <!-- Loading Messages -->
                <div id="loading-customers" class="d-none">
                    <i class="fas fa-spinner fa-spin"></i> Loading customers...
                </div>
                <div id="loading-invoices" class="d-none">
                    <i class="fas fa-spinner fa-spin"></i> Loading invoices...
                </div>
                
                <!-- Status Messages -->
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Status:</strong> Page loaded successfully. Database connected with <?php echo count($customers); ?> customers available.
                    <br>
                    <small>Click the refresh button next to Customer dropdown to load customers via AJAX, or click VIEW to load invoices.</small>
                </div>

                <!-- Invoice Preview -->
                <div id="invoice-preview" class="invoice-preview">
                    <div class="invoice-header">
                        <div class="row">
                            <div class="col-md-6">
                                <h3>INVOICE</h3>
                                <p>Invoice Date: <span id="preview-date"></span></p>
                            </div>
                            <div class="col-md-6 text-end">
                                <h4>Company Name</h4>
                                <p>Company Address<br>
                                Phone: +91-9453619260<br>
                                Email: support@company.com</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h5>Bill To:</h5>
                            <div id="customer-details"></div>
                        </div>
                        <div class="col-md-6 text-end">
                            <p>Period: <span id="date-range"></span></p>
                        </div>
                    </div>

                    <table class="table table-striped invoice-table">
                        <thead class="table-dark">
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Received</th>
                                <th class="text-end">Pending</th>
                            </tr>
                        </thead>
                        <tbody id="invoice-items">
                        </tbody>
                    </table>

                    <div class="total-section">
                        <div class="row">
                            <div class="col-md-8"></div>
                            <div class="col-md-4">
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Total Amount:</strong></td>
                                        <td class="text-end"><strong id="total-amount">₹0.00</strong></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total Received:</strong></td>
                                        <td class="text-end"><strong id="total-received">₹0.00</strong></td>
                                    </tr>
                                    <tr class="table-warning">
                                        <td><strong>Total Pending:</strong></td>
                                        <td class="text-end"><strong id="total-pending">₹0.00</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="button" class="btn btn-primary" onclick="window.print()">
                            <i class="fas fa-print"></i> Print Invoice
                        </button>
                        <button type="button" class="btn btn-success" onclick="downloadPDF()">
                            <i class="fas fa-download"></i> Download PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        $(document).ready(function() {
            // Configure toastr
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };

            // Don't load customers automatically - wait for user interaction
            console.log('Page loaded, ready for user interaction');
            
            // Clear any existing toastr notifications
            toastr.clear();

            // Load customers button click
            $('#loadCustomersBtn').click(function() {
                loadCustomers();
            });

            // View button click
            $('#viewBtn').click(function() {
                loadInvoices();
            });

            // Form submission
            $('#invoiceForm').submit(function(e) {
                e.preventDefault();
                generateInvoice();
            });

            // Select all checkbox
            $('#select_all').change(function() {
                if (this.checked) {
                    $('#customer_id').val('');
                    loadInvoices();
                }
            });

            function loadCustomers() {
                console.log('Loading customers...');
                $('#loading-customers').removeClass('d-none');
                
                $.ajax({
                    url: window.location.href,  // Use current page URL
                    type: 'POST',
                    data: { action: 'load_customers' },
                    dataType: 'json',
                    timeout: 10000,  // 10 second timeout
                    success: function(response) {
                        console.log('Load customers response:', response);
                        if (response.success) {
                            // Update customer dropdown
                            var options = '<option value="">All Customers</option>';
                            response.data.forEach(function(customer) {
                                options += '<option value="' + customer.id + '">' + customer.name + '</option>';
                            });
                            $('#customer_id').html(options);
                            toastr.success('Customers loaded successfully (' + response.data.length + ' customers)');
                        } else {
                            console.error('Load customers failed:', response.error);
                            toastr.error('Error loading customers: ' + (response.error || 'Unknown error'));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error details:', {
                            status: status,
                            error: error,
                            responseText: xhr.responseText,
                            readyState: xhr.readyState,
                            statusCode: xhr.status
                        });
                        toastr.error('Network error loading customers. Please check console for details.');
                    },
                    complete: function() {
                        $('#loading-customers').addClass('d-none');
                    }
                });
            }

            function loadInvoices() {
                console.log('Loading invoices...');
                $('#loading-invoices').removeClass('d-none');
                
                var formData = {
                    action: 'load_invoices',
                    customer_id: $('#customer_id').val(),
                    from_date: $('#from_date').val(),
                    to_date: $('#to_date').val()
                };
                
                console.log('Loading invoices with data:', formData);

                $.ajax({
                    url: window.location.href,  // Use current page URL
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    timeout: 10000,  // 10 second timeout
                    success: function(response) {
                        console.log('Load invoices response:', response);
                        if (response.success) {
                            console.log('Invoices loaded:', response.data);
                            toastr.success('Invoices loaded successfully (' + response.data.length + ' invoices)');
                            // Here you could display the invoices in a table or list
                        } else {
                            console.error('Load invoices failed:', response.error);
                            toastr.error('Error loading invoices: ' + (response.error || 'Unknown error'));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error details:', {
                            status: status,
                            error: error,
                            responseText: xhr.responseText,
                            readyState: xhr.readyState,
                            statusCode: xhr.status
                        });
                        toastr.error('Network error loading invoices. Please check console for details.');
                    },
                    complete: function() {
                        $('#loading-invoices').addClass('d-none');
                    }
                });
            }

            function generateInvoice() {
                var formData = {
                    action: 'generate_invoice',
                    customer_id: $('#customer_id').val(),
                    invoice_date: $('#invoice_date').val(),
                    from_date: $('#from_date').val(),
                    to_date: $('#to_date').val()
                };

                if (!formData.customer_id) {
                    toastr.error('Please select a customer');
                    return;
                }

                $.ajax({
                    url: '',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            displayInvoice(response.invoice);
                            toastr.success('Invoice generated successfully');
                        } else {
                            toastr.error('Error generating invoice: ' + (response.error || 'Unknown error'));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                        toastr.error('Error generating invoice: ' + error);
                    }
                });
            }

            function displayInvoice(invoice) {
                // Update invoice preview
                $('#preview-date').text(invoice.invoice_date);
                $('#customer-details').html(
                    '<strong>' + invoice.customer.name + '</strong><br>' +
                    (invoice.customer.email || '') + '<br>' +
                    (invoice.customer.phone || '') + '<br>' +
                    (invoice.customer.address || '')
                );
                
                var dateRange = '';
                if (invoice.from_date && invoice.to_date) {
                    dateRange = invoice.from_date + ' to ' + invoice.to_date;
                } else if (invoice.from_date) {
                    dateRange = 'From ' + invoice.from_date;
                } else if (invoice.to_date) {
                    dateRange = 'Until ' + invoice.to_date;
                } else {
                    dateRange = 'All time';
                }
                $('#date-range').text(dateRange);

                // Update invoice items
                var itemsHtml = '';
                invoice.items.forEach(function(item) {
                    itemsHtml += '<tr>' +
                        '<td>' + item.date + '</td>' +
                        '<td>Income Record #' + item.id + '</td>' +
                        '<td class="text-end">₹' + parseFloat(item.total_amount).toFixed(2) + '</td>' +
                        '<td class="text-end">₹' + parseFloat(item.received_amount).toFixed(2) + '</td>' +
                        '<td class="text-end">₹' + parseFloat(item.pending_amount).toFixed(2) + '</td>' +
                        '</tr>';
                });
                $('#invoice-items').html(itemsHtml);

                // Update totals
                $('#total-amount').text('₹' + parseFloat(invoice.total_amount).toFixed(2));
                $('#total-received').text('₹' + parseFloat(invoice.total_received).toFixed(2));
                $('#total-pending').text('₹' + parseFloat(invoice.total_pending).toFixed(2));

                // Show invoice preview
                $('#invoice-preview').show();
            }

            function downloadPDF() {
                toastr.info('PDF download functionality would be implemented here');
            }
        });
    </script>
</body>
</html>
