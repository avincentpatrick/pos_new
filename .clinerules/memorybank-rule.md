# Memory Bank

## Integrating Font Awesome Locally in a Laravel Vite Project

When replacing a Font Awesome CDN link with a local copy in a Laravel project using Vite, simply downloading the CSS file is not enough. The CSS file contains relative paths to font files (e.g., `.woff2`, `.ttf`) which also need to be available locally.

### Problem

After downloading the Font Awesome CSS and linking it locally, the browser console shows 404 (Not Found) errors for font files like `fa-solid-900.woff2`. This happens because the font files themselves were not downloaded.

### Solution

1.  **Create Directories**: Ensure the necessary directories exist in your `public` folder.
    ```bash
    mkdir public\css
    mkdir public\webfonts
    ```

2.  **Download CSS**: Download the Font Awesome CSS file into the `public/css` directory.
    ```bash
    curl -o public/css/fontawesome.css https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css
    ```

3.  **Download Font Files**: Download the required font files from the CDN's `webfonts` directory into your local `public/webfonts` directory. The paths are relative to the CSS file's location on the CDN.
    ```bash
    curl -o public/webfonts/fa-solid-900.woff2 https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/webfonts/fa-solid-900.woff2
    curl -o public/webfonts/fa-solid-900.ttf https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/webfonts/fa-solid-900.ttf
    ```
    *(Note: You may need to download other font files like `.svg`, `.eot` depending on browser support requirements.)*

4.  **Update Blade Template**: Modify the main layout file (e.g., `resources/views/layouts/app.blade.php`) to use the local CSS file with Laravel's `asset()` helper. The relative paths (`../webfonts/`) inside the CSS will now correctly point to your local `public/webfonts` directory.
    ```html
    <link rel="stylesheet" href="{{ asset('css/fontawesome.css') }}" />
    ```

This process ensures the project is self-contained and all assets are loaded correctly without relying on external CDNs.

## Project Analysis: Point of Sale (POS) System - Updated

### Overview

The analyzed project is a full-stack Point of Sale (POS) system designed for managing sales, inventory, and customer data. Recent updates have focused on enhancing the cash management process during shift operations, ensuring transaction integrity, refactoring payment handling, improving the delivery batch finalization process, and refining stock movement tracking for dispensed and returned items. New pages for Stock Movement Monitoring and Expense Management have been added for administrators. A new "Promos and Discounts" module has been added for managing promo packages and special price sets.

### Technology Stack

*   **Backend**: Laravel
*   **Frontend**: Livewire, Tailwind CSS, and Vite for asset management. This combination is often referred to as the TALL Stack.
*   **Database**: The application uses a relational database with tables for products, stocks, clients, sales, payment methods, duty logs, denominations, cash counts, cash count items, transactions, payments, delivery batches, stock movements, expenses, expense types, promo packages, special price sets, promos, special prices, client promos, and client special prices.

### Key Features

*   **Sales Management**: Components like `AddSale` and `CashierModule` handle the core sales process.
*   **Inventory Control**: `ProductList`, `StockList`, and `StorageModule` are used for managing product inventory.
*   **Customer Relationship Management**: `ClientList` suggests functionality for managing customer data.
*   **Administration**: An `AdminModule` is present for administrative oversight.
*   **Enhanced Cash Management**: Detailed cash denomination counting for both "Start Shift" and "End Shift" operations.
*   **Transaction Integrity**: Transactions are now explicitly linked to the active cashier's duty log.
*   **Remittance Page**: A new page for managing COD transactions and client credit payments.
    *   **Enhancement**: Displays both adjusted and original total amounts for COD transactions to indicate price adjustments due to returns.
*   **Delivery Batch Finalization**: Improved drag-and-drop functionality for assigning transactions to delivery batches, with temporary removal from pending list and permanent removal upon finalization. Visual enhancements for better UI contrast and consolidated batch information.
    *   **Enhancement**: Pending transactions for delivery now exclude returned items and require a dispense record. The table is real-time, updating every 5 seconds.
    *   **Enhancement**: `DeliveryBatch` status is updated to 'Completed' (ID 2) when any associated transaction is marked as 'Completed' or 'Returned' in `FulfillOrdersModule`.
*   **Fulfill Orders Module**: A new module has been implemented to manage pending and fulfilled sales orders. This includes:
    *   Displaying "Pending Transactions for Dispense" and "Fulfilled Orders" lists.
    *   A "Dispense" button for pending transactions that, when clicked, creates a record in the `stock_movements` table.
    *   Dynamic computation of stock based on the `stock_movements` table, without directly decrementing product stock in the `products` table.
    *   Enhanced "View" modal for fulfilled orders, allowing updates to status (Completed/Returned), capturing `actual_quantity_dispensed`, `actual_quantity_returned`, `return_reason_id`, and `return_remarks`.
    *   **Enhancement**: When a transaction is marked as 'Completed' or 'Returned', its parent `DeliveryBatch` is updated to 'Completed' (ID 2). The pending transactions table is real-time, updating every 5 seconds.
*   **Stock Movement Monitoring**: A new page for administrators to monitor stock movements in real-time.
    *   **Enhancement**: Replaced searchable dropdown for storage shifts with a date picker. Displays selected storage shift details (User, Time In, Time Out).
    *   **Enhancement**: Discrepancy formula in product summary is `Closing Count - Running Stock`, where `Running Stock = Start Count + Restocks - Initial Dispensed + Returns`.
*   **Expense Management**: A new page for administrators to manage expenses.
    *   **Features**: List expenses, search by type or specify, filter by expense type, pagination, and an "Add Expense" modal.
    *   **CRUD**: Implemented Add, Edit, and Delete (with admin password confirmation) functionalities.
    *   **Conditional Display**: `expense_type_specify` field is only shown if `expense_type_id` is 8 (for 'Other' expenses).
*   **Promos and Discounts**: A new module for managing promo packages and special price sets.
    *   **Features**: List promo packages and special price sets, search, pagination, and add/edit/delete functionality.
    *   **Client Application**: Ability to apply promo packages and special price sets to clients. A client can have one promo package and one special price set at a time.
    *   **Sales Integration**: The `AddSale` component automatically applies special prices and promos for the selected client.

### Database Schema Updates & Relationships

The following tables and their relationships are crucial for the enhanced cash management and transaction integrity:

*   **`cashier_duty_logs`**: This table serves as the parent for shift-related activities.
    *   **Removed Columns**: `total_cash_in`, `total_cash_out`, `total_sales_in_cash` have been removed from this table, as these values are now managed through associated `cash_counts` records.
    *   **Relationships**:
        *   `hasOne(CashCount::class)->where('count_type_id', 1)`: Represents the starting cash count for the shift.
        *   `hasOne(CashCount::class)->where('count_type_id', 2)`: Represents the ending cash count for the shift.

*   **`cash_counts`**: This table stores the total amount for a specific cash count (start or end of shift).
    *   `id`: Primary Key
    *   `cashier_duty_log_id`: Foreign key linking to the `cashier_duty_logs` table.
    *   `count_type_id`: Differentiates between 'Start Shift' (1) and 'End Shift' (2) cash counts.
    *   `total_amount`: The total amount of the cash count.
    *   **Removed Columns**: `user_id` and `created_by` have been removed from this table.
    *   **Relationships**:
        *   `belongsTo(CashierDutyLog::class)`
        *   `hasMany(CashCountItem::class)`

*   **`cash_count_items`**: This table stores the detailed breakdown of each cash count by denomination.
    *   `id`: Primary Key
    *   `cash_count_id`: Foreign key linking to the `cash_counts` table.
    *   `denomination_id`: Foreign key for the specific denomination.
    *   `quantity`: The quantity of that denomination.
    *   `sub_total`: The total value for that specific denomination (quantity * denomination value).
    *   **Removed Columns**: `created_by` have been removed from this table.
    *   **Relationships**:
        *   `belongsTo(CashCount::class)`
        *   `belongsTo(Denomination::class)`

*   **`denominations`**: This table stores the available currency denominations.
    *   `id`: Primary Key
    *   `denomination_name`: Name of the denomination (e.g., "₱1000").
    *   `value`: Numeric value of the denomination.
    *   **Relationships**:
        *   `hasMany(CashCountItem::class)`

*   **`transactions`**: This table stores the details of each transaction.
    *   **Added Columns**: `cashier_duty_log_id` to link transactions to the active cashier's shift. `total_amount` (moved back from `payments` table).
    *   **Removed Columns**: `remaining_balance` (now a dynamic accessor).
    *   **Relationships**:
        *   `belongsTo(CashierDutyLog::class)`: Links a transaction to the cashier's duty log during which it occurred.
        *   `hasMany(Sale::class)`: A transaction can have multiple sales.
        *   `hasMany(Payment::class)`: A transaction can have multiple payments.
        *   `belongsTo(OrderType::class)`: Links to the order type of the transaction.
    *   **Accessors**:
        *   `getRemainingBalanceAttribute()`: Dynamically calculates `adjusted_total - payments->sum('amount_received')`.
        *   `getAdjustedTotalAttribute()`: Dynamically calculates the total amount considering returned items from `StockMovement`.
        *   `getDispenseStatusAttribute()`: Dynamically calculates the dispense status of a transaction based on its sales and their stock movements.

*   **`payments`**: This table stores the details of each payment event.
    *   `id`: Primary Key
    *   `client_id`: Foreign key for the client.
    *   `transaction_id`: Foreign key linking to the `transactions` table.
    *   `payment_method_id`: Foreign key for the payment method.
    *   `amount_received`: The amount received for this specific payment.
    *   `amount_change`: The change given for this specific payment.
    *   `reference_number`: A reference number for the payment.
    *   `check_number`: The check number if paid by check.
    *   `created_by`: Foreign key for the user who created the payment.
    *   `updated_by`: Foreign key for the user who last updated the payment.
    *   `timestamps`: Created at and updated at timestamps.
    *   **Removed Columns**: `total_amount`, `remaining_balance` (now managed on `transactions` table).
    *   **Relationships**:
        *   `belongsTo(Client::class)`
        *   `belongsTo(Transaction::class)`
        *   `belongsTo(PaymentMethod::class)`

*   **`delivery_batches`**: This table manages batches of deliveries.
    *   **Added Columns**: `delivery_batch_status_id` (integer, default 1 for 'ongoing delivery').
    *   **Relationships**:
        *   `belongsTo(DeliveryBatchStatusType::class)`: Links to the status of the delivery batch.

*   **`stock_movements`**: This table tracks stock changes due to dispensing or returns.
    *   `id`: Primary Key
    *   **Removed Columns**: `stock_movement_id`, `movement_type_id` (no longer needed as `actual_quantity_dispensed` and `actual_quantity_returned` directly manage stock changes).
    *   `dispense_status_type_id`: 1 for returned, 2 for ongoing delivery, 3 for completed.
    *   `return_reason_id`: Foreign key for return reasons (nullable).
    *   `return_reason_specify`: Text field for specific return reasons (nullable).
    *   `actual_quantity_dispensed`: The quantity of product actually dispensed (nullable).
    *   `actual_quantity_returned`: The quantity of product returned (nullable).
    *   `return_remarks`: Text field for return remarks (nullable).
    *   `sales_id`: Foreign key linking to the `sales` table.
    *   `product_id`: Foreign key linking to the `products` table.
    *   `quantity`: Original quantity of product in the sale.
    *   **Relationships**:
        *   `belongsTo(Sale::class, 'sales_id')`
        *   `belongsTo(Product::class)`
        *   `belongsTo(DispenseStatusType::class, 'dispense_status_type_id')`
        *   `belongsTo(ReturnReason::class, 'return_reason_id')`

*   **`expenses`**: This table stores expense records.
    *   `id`: Primary Key
    *   `expense_type_id`: Foreign key linking to `expense_types` table.
    *   `expense_type_specify`: Text field for specifying 'Other' expense types (nullable).
    *   `amount`: The amount of the expense.
    *   `timestamps`: Created at and updated at timestamps.
    *   **Relationships**:
        *   `belongsTo(ExpenseType::class)`

*   **`expense_types`**: This table stores predefined expense types.
    *   `id`: Primary Key
    *   `expense_type_name`: Name of the expense type (e.g., "Electricity", "Water").
    *   `timestamps`: Created at and updated at timestamps.
    *   **Relationships**:
        *   `hasMany(Expense::class)`

*   **`promo_packages`**: This table stores promo packages.
    *   `id`: Primary Key
    *   `promo_package_name`: Name of the promo package.
    *   `validity_date`: Validity date of the promo package.
    *   **Relationships**:
        *   `hasMany(Promo::class)`
        *   `hasMany(ClientPromo::class)`

*   **`special_price_sets`**: This table stores special price sets.
    *   `id`: Primary Key
    *   `special_price_set_name`: Name of the special price set.
    *   `validity_date`: Validity date of the special price set.
    *   **Relationships**:
        *   `hasMany(SpecialPrice::class)`
        *   `hasMany(ClientSpecialPrice::class)`

*   **`promos`**: This table stores individual promos.
    *   `id`: Primary Key
    *   `promo_package_id`: Foreign key linking to `promo_packages` table.
    *   `product_id`: Foreign key linking to `products` table.
    *   `minimum_buy`: Minimum quantity to avail the promo.
    *   `get_free`: Quantity of free product.
    *   **Relationships**:
        *   `belongsTo(PromoPackage::class)`
        *   `belongsTo(Product::class)`

*   **`special_prices`**: This table stores individual special prices.
    *   `id`: Primary Key
    *   `special_price_set_id`: Foreign key linking to `special_price_sets` table.
    *   `product_id`: Foreign key linking to `products` table.
    *   `special_price`: The special price for the product.
    *   **Relationships**:
        *   `belongsTo(SpecialPriceSet::class)`
        *   `belongsTo(Product::class)`

*   **`client_promos`**: This table links clients to promo packages.
    *   `id`: Primary Key
    *   `client_id`: Foreign key linking to `clients` table.
    *   `promo_package_id`: Foreign key linking to `promo_packages` table.
    *   **Relationships**:
        *   `belongsTo(Client::class)`
        *   `belongsTo(PromoPackage::class)`

*   **`client_special_prices`**: This table links clients to special price sets.
    *   `id`: Primary Key
    *   `client_id`: Foreign key linking to `clients` table.
    *   `special_price_set_id`: Foreign key linking to `special_price_sets` table.
    *   **Relationships**:
        *   `belongsTo(Client::class)`
        *   `belongsTo(SpecialPriceSet::class)`

### Livewire Component Enhancements

*   **`app/Livewire/CashierModule.php`**:
    *   **Properties**: `$denominations`, `$cashCountQuantities`, `$endShiftCashCountQuantities`.
    *   **Computed Properties**: `totalCashIn`, `totalCashOutComputed`.
    *   **Methods**:
        *   `mount()`: Initializes denominations and quantity arrays (now cached).
        *   `openStartShiftModal()` / `openEndShiftModal()`: Resets validation and quantity arrays when modals are opened.
        *   `startShift()`: Creates `CashierDutyLog`, `CashCount` (type 1), and `CashCountItem` records.
        *   `endShift()`: Validates, creates `CashCount` (type 2) and `CashCountItem` records, updates `CashierDutyLog` status, and calculates `totalSalesInCash` by summing `amount_received` from `payments` relationship. Uses dynamic `remaining_balance` from `Transaction` for discrepancy.
        *   `prepareDailyReport()`: Retrieves starting/ending cash, calculates `salesByPaymentMethod` by summing `amount_received` from `payments`, and `salesByProduct` from `Transaction` sales. `PaymentMethod::all()` is now cached.
        *   **Fix**: Corrected `salesByPaymentMethod` calculation to iterate through transaction payments.
        *   **Fix**: Handled "Undefined array key" error in `resources/views/livewire/cashier-module.blade.php` by using null coalescing operator.

*   **`app/Livewire/AddSale.php`**:
    *   **Properties**: `$search`, `$selectedCustomerId`, `$quantities`, `$total_amount`, `$selectedOrderType`, `$notes`, `$paymentMethodId`, `$paymentAmountReceived`, `$paymentAmountChange`, `$paymentReferenceNumber`, `$paymentCheckNumber`, `$clientPromo`, `$clientSpecialPrices`.
    *   **Methods**:
        *   `openAddClientModal()`: Resets client fields, pre-fills `name` with `search` term.
        *   `openCheckoutModal()`: Resets payment fields.
        *   `checkout()`: Validates, conditionally sets initial payment details for COD/Credit, then calls `finalizeSale` or `openCheckoutModal`.
        *   `updatedPaymentAmountReceived()`: Calculates `paymentAmountChange` based on `amount_received` and `total_amount`.
        *   `finalizeSale()`: Validates, creates `Transaction` (with `total_amount`), and *always* creates `Payment` records (even for Credit or COD). Eager loads `payments` for `lastTransaction`. Applies special prices and promos.
        *   `resetForm()`: Resets all relevant properties.
        *   `addClient()`: Creates new client.
        *   `selectCustomer()` / `clearSelectedCustomer()`: Manages selected customer, fetches their promo and special prices.
        *   `incrementQuantity()` / `decrementQuantity()` / `updatedQuantities()`: Manages product quantities.
        *   `calculateTotal()`: Calculates `total_amount` based on special prices if available.
        *   `render()`: Fetches customers, products, payment methods (cached), and order types (cached).

*   **`app/Livewire/TransactionList.php`**:
    *   **Properties**: `$search`, `$startDate`, `$endDate`, `$showReceiptModal`, `$selectedTransaction`, `$paymentMethodFilter`, `$statusFilter`.
    *   **Methods**:
        *   `viewTransaction()`: Eager loads `client`, `sales.product`, `payments.paymentMethod`.
        *   `render()`: Queries transactions, eager loads `client`, `payments.paymentMethod`, filters by date range, `paymentMethodFilter`, and `statusFilter`.
            *   **Status Filter Logic**:
                *   `unpaid`: Filters for COD transactions (`payment_method_id = 7`) where `total_amount > sum(amount_received)`.
                *   `paid`: Filters for transactions (excluding Client Credit `payment_method_id = 3`) where `total_amount <= sum(amount_received)`. This includes all fully paid transactions (Cash, Card, Check, Paymaya, and fully paid COD).
                *   `not_applicable`: Filters for Client Credit transactions (`payment_method_id = 3`).
        *   **New Feature**: Added a "Dispense Status" column to the transaction list, with conditional styling.
        *   **New Feature**: Replaced the "Edit" button with a conditional "Delete" button that appears only for transactions with a "Pending" dispense status. The deletion is protected by an admin password confirmation.

*   **`app/Livewire/Remittances.php`**:
    *   **Properties**: `$showAddPaymentModal`, `$transactionId`, `remittancePaymentMethodId`, `remittanceAmountReceived`, `remittanceReferenceNumber`, `remittanceCheckNumber`, `remittanceAmountChange`, `$searchClient`, `$filteredClients`, `$selectedClientId`, `$selectedClientName`.
    *   **Methods**:
        *   `mount()`: Initializes denominations and quantity arrays (now cached). `filteredClients` is initialized as an empty collection.
        *   `openAddPaymentModal($transactionId)`: Fetches `Transaction`, initializes remittance form with `transaction->remaining_balance`.
        *   `openAddClientPaymentModal()`: Resets `searchClient`, `filteredClients`, `selectedClientId`, `selectedClientName`, `remittancePaymentMethodId`, `remittanceAmountReceived`, `remittanceReferenceNumber`, `remittanceCheckNumber`, `remittanceAmountChange`.
        *   `updatedSearchClient($value)`: Filters clients based on search input, clears `selectedClientId` and `selectedClientName`, and manages `filteredClients`.
        *   `selectClient($clientId)`: Sets the selected client and clears `filteredClients`.
        *   `clearSelectedClient()`: Resets client selection.
        *   `updatedRemittanceAmountReceived()`: Calculates `remittanceAmountChange` based on `received` and `transaction->total_amount`.
        *   `addRemittancePayment()`: Validates, creates a *new* `Payment` record linked to the `Transaction`, and implicitly updates `Transaction`'s dynamic `remaining_balance`.
        *   `render()`: Fetches COD transactions and client credit payments (both now query `Transaction` with `payments` relationship and filter by dynamic `remaining_balance`). `PaymentMethod::all()` is cached. Includes logic to re-filter clients if `searchClient` is set and no client is selected.
        *   **Enhancement**: Added listener for `transactionUpdated` event to refresh data on returns.

*   **`app/Livewire/FinalizeDeliveryBatch.php`**:
    *   **Properties**: `$pendingTransactionsByBatch` (stores full `Transaction` objects).
    *   **Methods**:
        *   `loadTransactions()`:
            *   Excludes transactions that are currently in `$pendingTransactionsByBatch`.
            *   **Enhancement**: Now excludes any transaction with an associated `Delivery` record (removed 'Returned' exception).
            *   **Enhancement**: Requires a `sales.stockMovement` record (dispense record) to be present.
        *   `transactionDropped($transactionId, $batchId)`:
            *   Fetches the full `Transaction` model and stores it in `$pendingTransactionsByBatch[$batchId]`.
            *   Calls `loadTransactions()` to refresh the pending list.
        *   `finalizeBatchDelivery($batchId)`:
            *   Iterates through `Transaction` objects in `$pendingTransactionsByBatch[$batchId]` to create `Delivery` records.
            *   Clears `$pendingTransactionsByBatch[$batchId]` and refreshes `loadTransactions()` and `loadActiveDeliveryBatches()`.
        *   `printSelectedBatches()`:
            *   Renders `delivery-batch-print.blade.php` with selected batches.
            *   Returns a JavaScript response to open a new window and print the rendered HTML.
        *   **Enhancement**: `render()` method now calls `loadTransactions()` to ensure real-time updates on poll.
        *   **Enhancement**: `wire:key` attributes added to delivery batch elements in the Blade view for better reactivity.

*   **`app/Livewire/FulfillOrdersModule.php`**:
    *   **Properties**: `$pendingTransactions`, `$fulfilledOrders`, `$returnReasons`, `$selectedStatusId`, `$returnReasonId`, `$specifyReason`, `$actualQuantityReturned`, `$return_remarks`, `$actualQuantityDispensed`.
    *   **Methods**:
        *   `render()`: Contains the primary logic for fetching and filtering both pending and fulfilled orders.
            *   **Pending Transactions Query**: Fetches `Sale` models with `product`, `transaction.orderType`, and `transaction.client` relationships. It filters for sales that have no `stockMovements`.
            *   **Fulfilled Orders Query**: Fetches `Sale` models with `product`, `transaction.orderType`, `transaction.client`, and `stockMovements.dispenseStatusType` relationships. It filters for sales that have a `stockMovement` record with `dispense_status_type_id` of 'Ongoing Delivery' or 'Completed'.
        *   `dispenseOrder($saleId)`: Creates a `StockMovement` record to mark an order as dispensed. For "Pick-up" orders, `actual_quantity_dispensed` is automatically set to the `sale->quantity`.
        *   `viewOrder($saleId)`: Loads a specific sale's details into a modal, initializes `$selectedStatusId`, `$actualQuantityDispensed`, `$actualQuantityReturned`, and `$return_remarks` from the existing `stockMovement` record. Also loads all `ReturnReason` options.
        *   `updateFulfilledOrder()`: Validates inputs. If `selectedStatusId` is 'Returned' (1), it updates the existing `StockMovement` record with `dispense_status_type_id = 1`, `return_reason_id`, `return_reason_specify`, `return_remarks`, `actual_quantity_dispensed`, and `actual_quantity_returned`. If `selectedStatusId` is 'Completed' (3), it updates the existing `StockMovement` record with `dispense_status_type_id = 3`, sets `actual_quantity_dispensed` to the original `sale->quantity`, and clears all return-related fields (`return_reason_id`, `return_reason_specify`, `actual_quantity_returned`, `return_remarks`).
            *   **Enhancement**: Updates parent `DeliveryBatch` status to 'Completed' (ID 2) and dispatches `deliveryBatchUpdated` event.
        *   `viewOrder($saleId)`: Loads a specific sale's details into a modal.
        *   `confirmTagSelectedAsCompleted()`: Updates the `dispense_status_type_id` for selected orders to "Completed".
            *   **Enhancement**: Updates parent `DeliveryBatch` status to 'Completed' (ID 2) and dispatches `deliveryBatchUpdated` event.
        *   **Enhancement**: The pending transactions table is real-time, updating every 5 seconds.

*   **`app/Livewire/StorageModule.php`**:
    *   **Properties**: `$shiftStarted`, `$showStartShiftModal`, `$showEndShiftModal`, `$showShiftReportModal`, `$products`, `$stockCountQuantities`, `$endShiftStockCountQuantities`, `$currentStorageDutyLogId`, `$shiftReport`, `$selectedStorageDutyLog`.
    *   **Methods**:
        *   `mount()`: Initializes products and stock quantity arrays.
        *   `checkShiftStatus()`: Checks for an active storage duty log.
        *   `openStartShiftModal()`: Resets validation and quantities for the start shift modal.
        *   `startShift()`: Creates `StorageDutyLog`, `StockCount` (type 1), and `StockCountItems` records. **Enhancement**: Now also creates `Stock` records for each product with a quantity greater than 0, linking them to the current `storage_duty_log_id`.
        *   `openEndShiftModal()`: Resets validation and quantities for the end shift modal.
        *   `endShift()`: Validates, updates `StorageDutyLog` status, creates `StockCount` (type 2) and `StockCountItems` records. Generates a shift report. **Update**: The `systemComputedRemainingStock` calculation now excludes `startQuantity` (i.e., `restocks - initialDispensed + returns`) because starting quantities are now added directly to `stocks` and are thus included in `restocks`.
        *   `render()`: Renders the storage module view.

*   **`app/Livewire/StockMovementMonitoring.php`**:
    *   **Properties**: `$selectedProductId`, `$selectedStorageDutyLogId`, `$selectedDate`, `$selectedStorageDutyLog`, `$startDate`, `$endDate`, `$currentActiveStorageDutyLogId`, `$products`, `$storageDutyLogs`, `$productSummary`.
    *   **Methods**:
        *   `mount()`: Initializes products, storage duty logs, and date filters. Sets default `selectedStorageDutyLogId` and `selectedDate` to the current active shift if one exists.
        *   `render()`: Fetches and combines stock additions and dispensing movements into a single, sorted timeline. Calculates a product summary based on the filtered data.
        *   `calculateProductSummary()`: Calculates the "Start Count", "Restocks", "Dispensed", "Returns", "Running Stock", and "Loss" for each product based on the filtered data. **Update**: The `runningStock` calculation now excludes `startCount` (i.e., `restocks - initialDispensed + returns`) because starting quantities are now added directly to `stocks` and are thus included in `restocks`.
        *   The component uses `wire:poll.5s` for real-time updates.
        *   **Enhancement**: Replaced searchable dropdown for storage shifts with a date picker. Displays selected storage shift details (User, Time In, Time Out).
        *   **Enhancement**: Discrepancy formula in product summary is `Closing Count - Running Stock`, where `Running Stock = Start Count + Restocks - Initial Dispensed + Returns`.

*   **`app/Livewire/ClientDetail.php`**:
    *   **Enhancement**: "Total Sales" now sums `adjusted_total` of all transactions.
    *   **Enhancement**: "Total Credit" now sums `adjusted_total` of transactions with `payment_method_id = 3` (Credit).

*   **`app/Livewire/ExpenseList.php`**:
    *   **New Feature**: Expense management page with list, search, filter, pagination, and add/edit/delete functionality.
    *   **Conditional Display**: `expense_type_specify` field is only shown if `expense_type_id` is 8.

### Frontend Enhancements

*   **`resources/views/livewire/client-list.blade.php`**:
    *   "Add Client" button conditional display (only when no search results).
    *   "Name" field in modals marked as required with a red asterisk.

*   **`resources/views/livewire/add-sale.blade.php`**:
    *   Debounce (`.debounce.500ms`) added to product quantity input fields.
    *   "Checkout Modal" and "Receipt Modal" updated to reflect `total_amount` from `Transaction` and dynamic `remaining_balance`.
    *   **New Feature**: Receipts now provide a breakdown of paid and free items.
    *   **New Feature**: Print functionality now isolates the receipt content and formats it for thermal printers.

*   **`resources/views/livewire/cashier-module.blade.php`**:
    *   Debounce (`.debounce.500ms`) added to cash denomination quantity input fields in "Start Shift" and "End Shift" modals.
    *   `href="{{ route('add-remittance') }}"` changed to `href="{{ route('remittances') }}"`.

*   **`resources/views/livewire/transaction-list.blade.php`**:
    *   Date range filter added with "Start Date" and "End Date" inputs (max date set to today).
    *   Transaction table and "Receipt Modal" updated to display `total_amount` and dynamic `remaining_balance` from `Transaction`, and payment details from the `payments` relationship.
    *   **New Feature**: Receipts now provide a breakdown of paid and free items, consistent with the `add-sale` page.
    *   **New Feature**: Print functionality now isolates the receipt content and formats it for thermal printers.
*   **`resources/views/livewire/fulfill-orders-module.blade.php`**:
    *   Added a "Customer Name" column to both the "Pending Transactions for Dispense" and "Fulfilled Orders" tables to display the client's name associated with each transaction.
    *   **Inline Status Display**:
        *   "N/A (Credit)" for Client Credit transactions (`payment_method_id = 3`).
        *   "Unpaid" with remaining balance for COD transactions (`payment_method_id = 7`) where `total_amount > sum(amount_received)`.
        *   "Paid" for all other transactions (including fully paid COD, Cash, Card, Check, Paymaya, etc.).
    *   **Status Filter Options**: Added "Unpaid (COD)", "Paid", and "N/A (Credit)" options to the status filter dropdown.
    *   **Details Modal Enhancements**:
        *   Dropdown for "Update Status" (Completed/Returned).
        *   Conditional fields for "Actual Quantity Dispensed", "Actual Quantity Returned", "Reason for Return", "Specify Reason" (if "Others" is selected), and "Return Remarks" are displayed only when "Returned" status is selected.
    *   **Enhancement**: The pending transactions table is real-time, updating every 5 seconds.

*   **`resources/views/livewire/remittances.blade.php`**:
    *   The `amount_received` field in the "Add Client Credit Payment" modal is no longer conditionally rendered and is always visible.
    *   The client search input (`searchClient`) is now disabled when a client is selected (`@if($selectedClientId) disabled @endif`).
    *   The filtered client list is displayed as an absolute-positioned overlay (`absolute z-10`).
    *   The "Clear Selected Client" button is now a `x-secondary-button` positioned below the search input, appearing only when a client is selected (`@if ($selectedClientId)`).
