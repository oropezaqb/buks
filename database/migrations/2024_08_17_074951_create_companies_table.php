<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->unique(['code']);
            $table->timestamps();
        });
        Schema::create('company_user', function (Blueprint $table) {
            $table->primary(['user_id', 'company_id']);
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
        });
        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id');
            $table->string('name');
            $table->unique(['name', 'company_id']);
            $table->string('label')->nullable();
            $table->timestamps();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
        });
        Schema::create('abilities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id');
            $table->string('name');
            $table->unique(['name', 'company_id']);
            $table->string('label')->nullable();
            $table->timestamps();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
        });
        Schema::create('ability_role', function (Blueprint $table) {        
            $table->primary(['role_id', 'ability_id']);
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('ability_id');
            $table->timestamps();
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');
            $table->foreign('ability_id')
                ->references('id')
                ->on('abilities')
                ->onDelete('cascade');
        });
        Schema::create('role_user', function (Blueprint $table) {
            $table->primary(['user_id', 'role_id']);
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
            $table->timestamps();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');
        });
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('company_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->timestamps();
        });
        Schema::create('current_company', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
        });
        Schema::create('line_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name');
            $table->unique(['company_id', 'name']);
            $table->timestamps();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
        });
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('number');
            $table->string('title');
            $table->unique(['company_id', 'title']);
            $table->enum('type',
            [
                '110 - Cash and Cash Equivalents',
                '120 - Non-Cash Current Asset',
                '150 - Non-Current Asset',
                '210 - Current Liabilities',
                '250 - Non-Current Liabilities',
                '310 - Capital',
                '320 - Share Premium',
                '330 - Retained Earnings',
                '340 - Other Comprehensive Income',
                '350 - Drawing',
                '390 - Income Summary',
                '410 - Revenue',
                '420 - Other Income',
                '510 - Cost of Goods Sold',
                '520 - Operating Expense',
                '590 - Income Tax Expense'
            ]);
            $table->unsignedBigInteger('line_item_id')->nullable();
            $table->boolean('subsidiary_ledger');
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->foreign('line_item_id')
                ->references('id')
                ->on('line_items');
            $table->timestamps();
        });
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique;
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
        });
        Schema::create('subsidiary_ledgers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('number')->nullable();
            $table->string('name');
            $table->unique(['name', 'company_id']);
            $table->timestamps();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
        });
        Schema::create('report_line_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('report');
            $table->string('section')->nullable();
            $table->string('line_item');
            $table->unique(['company_id', 'report', 'section', 'line_item']);
            $table->timestamps();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
        });
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->date('date');
            $table->unsignedBigInteger('document_type_id');
            $table->unsignedBigInteger('document_number')->nullable();
            $table->text('explanation');
            $table->timestamps();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->foreign('document_type_id')
                ->references('id')
                ->on('documents');
            $table->nullableMorphs('journalizable');
        });
        Schema::create('postings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('journal_entry_id');
            $table->unsignedBigInteger('account_id');
            $table->decimal('debit', 13, 2);
            $table->unsignedBigInteger('subsidiary_ledger_id')->nullable();
            $table->unsignedBigInteger('report_line_item_id')->nullable();
            $table->timestamps();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->foreign('journal_entry_id')
                ->references('id')
                ->on('journal_entries')
                ->onDelete('cascade');
            $table->foreign('account_id')
                ->references('id')
                ->on('accounts');
            $table->foreign('subsidiary_ledger_id')
                ->references('id')
                ->on('subsidiary_ledgers');
            $table->foreign('report_line_item_id')
                ->references('id')
                ->on('report_line_items');
        });
        Schema::create('queries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('category');
            $table->string('title');
            $table->text('query');
            $table->unsignedBigInteger('ability_id');
            $table->unique(['title', 'company_id']);
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->foreign('ability_id')
                ->references('id')
                ->on('abilities')
                ->onDelete('cascade');
            $table->timestamps();
        });
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name');
            $table->unique(['name', 'company_id']);
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->timestamps();
        });
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name');
            $table->boolean('track_quantity');
            $table->unsignedBigInteger('receivable_account_id');
            $table->unsignedBigInteger('inventory_account_id')->nullable();
            $table->unsignedBigInteger('income_account_id');
            $table->unsignedBigInteger('expense_account_id')->nullable();
            $table->unique(['name', 'company_id']);
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->foreign('receivable_account_id')
                ->references('id')
                ->on('accounts');
            $table->foreign('inventory_account_id')
                ->references('id')
                ->on('accounts');
            $table->foreign('income_account_id')
                ->references('id')
                ->on('accounts');
            $table->foreign('expense_account_id')
                ->references('id')
                ->on('accounts');
            $table->timestamps();
        });
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('supplier_id');
            $table->date('bill_date');
            $table->date('due_date');
            $table->unsignedBigInteger('bill_number');
            $table->unique(['company_id', 'supplier_id', 'bill_number'], 'my_unique_ref');
            $table->timestamps();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->foreign('supplier_id')
                ->references('id')
                ->on('suppliers');
        });
        Schema::create('bill_category_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bill_id');
            $table->unsignedBigInteger('account_id');
            $table->text('description')->nullable();
            $table->decimal('amount', 13, 2);
            $table->decimal('input_tax', 13, 2)->default(0);
            $table->timestamps();
            $table->foreign('bill_id')
                ->references('id')
                ->on('bills')
                ->onDelete('cascade');
            $table->foreign('account_id')
                ->references('id')
                ->on('accounts');
        });
        Schema::create('bill_item_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bill_id');
            $table->unsignedBigInteger('product_id');
            $table->text('description')->nullable();
            $table->float('quantity', 8, 2)->nullable();
            $table->decimal('amount', 13, 2);
            $table->decimal('input_tax', 13, 2)->default(0);
            $table->timestamps();
            $table->foreign('bill_id')
                ->references('id')
                ->on('bills')
                ->onDelete('cascade');
            $table->foreign('product_id')
                ->references('id')
                ->on('products');
        });
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->date('date');
            $table->unsignedBigInteger('product_id');
            $table->float('quantity', 8, 2)->nullable();
            $table->decimal('amount', 13, 2);
            $table->timestamps();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->foreign('product_id')
                ->references('id')
                ->on('products');
            $table->morphs('purchasable');
        });
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name');
            $table->unique(['name', 'company_id']);
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->timestamps();
        });
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('customer_id');
            $table->date('date');
            $table->date('due_date');
            $table->unsignedBigInteger('invoice_number');
            $table->timestamps();
            $table->unique(['company_id', 'invoice_number'], 'my_unique_ref');
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->foreign('customer_id')
                ->references('id')
                ->on('customers');
        });
        Schema::create('invoice_item_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('product_id');
            $table->text('description')->nullable();
            $table->float('quantity', 8, 2)->nullable();
            $table->decimal('amount', 13, 2);
            $table->decimal('output_tax', 13, 2)->default(0);
            $table->timestamps();
            $table->foreign('invoice_id')
                ->references('id')
                ->on('invoices')
                ->onDelete('cascade');
            $table->foreign('product_id')
                ->references('id')
                ->on('products');
        });
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('purchase_id');
            $table->date('date');
            $table->unsignedBigInteger('product_id');
            $table->float('quantity', 8, 2)->nullable();
            $table->decimal('amount', 13, 2);
            $table->timestamps();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->foreign('product_id')
                ->references('id')
                ->on('products');
            $table->foreign('purchase_id')
                ->references('id')
                ->on('purchases')
                ->onDelete('cascade');
            $table->morphs('salable');
        });
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('type');
            $table->date('date');
            $table->timestamps();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->morphs('transactable');
        });
        $this->createTable('sales_receipts');
        Schema::create('sales_receipt_item_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_receipt_id');
            $table->unsignedBigInteger('product_id');
            $table->text('description')->nullable();
            $table->float('quantity', 8, 2)->nullable();
            $table->decimal('amount', 13, 2);
            $table->decimal('output_tax', 13, 2)->default(0);
            $table->timestamps();
            $table->foreign('sales_receipt_id')
                ->references('id')
                ->on('sales_receipts')
                ->onDelete('cascade');
            $table->foreign('product_id')
                ->references('id')
                ->on('products');
        });
        $this->createTable('received_payments');
        Schema::create('received_payment_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('received_payment_id');
            $table->unsignedBigInteger('invoice_id');
            $table->decimal('amount', 13, 2);
            $table->timestamps();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->foreign('invoice_id')
                ->references('id')
                ->on('invoices');
            $table->foreign('received_payment_id')
                ->references('id')
                ->on('received_payments')
                ->onDelete('cascade');
        });
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('invoice_id');
            $table->date('date');
            $table->unsignedBigInteger('number');
            $table->timestamps();
            $table->unique(['company_id', 'number'], 'my_unique_ref');
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->foreign('invoice_id')
                ->references('id')
                ->on('invoices');
        });
        Schema::create('credit_note_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('credit_note_id');
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('product_id');
            $table->text('description')->nullable();
            $table->float('quantity', 8, 2)->nullable();
            $table->decimal('amount', 13, 2);
            $table->decimal('output_tax', 13, 2)->default(0);
            $table->timestamps();
            $table->foreign('credit_note_id')
                ->references('id')
                ->on('credit_notes')
                ->onDelete('cascade');
            $table->foreign('invoice_id')
                ->references('id')
                ->on('invoices');
            $table->foreign('product_id')
                ->references('id')
                ->on('products');
        });
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('sale_id');
            $table->date('date');
            $table->unsignedBigInteger('product_id');
            $table->float('quantity', 8, 2)->nullable();
            $table->decimal('amount', 13, 2);
            $table->timestamps();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->foreign('product_id')
                ->references('id')
                ->on('products');
            $table->foreign('sale_id')
                ->references('id')
                ->on('sales')
                ->onDelete('cascade');
            $table->morphs('returnable_sale');
        });
        Schema::create('supplier_credits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('supplier_id');
            $table->morphs('purchasable');
            $table->date('date');
            $table->unsignedBigInteger('number');
            $table->unique(['company_id', 'supplier_id', 'purchasable_type',  'number'], 'my_unique_ref');
            $table->timestamps();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->foreign('supplier_id')
                ->references('id')
                ->on('suppliers');
        });
        Schema::create('supplier_credit_clines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_credit_id');
            $table->morphs('purchasable');
            $table->unsignedBigInteger('account_id');
            $table->text('description')->nullable();
            $table->decimal('amount', 13, 2);
            $table->decimal('input_tax', 13, 2)->default(0);
            $table->timestamps();
            $table->foreign('supplier_credit_id')
                ->references('id')
                ->on('supplier_credits')
                ->onDelete('cascade');
            $table->foreign('account_id')
                ->references('id')
                ->on('accounts');
        });
        Schema::create('supplier_credit_ilines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_credit_id');
            $table->morphs('purchasable');
            $table->unsignedBigInteger('product_id');
            $table->text('description')->nullable();
            $table->float('quantity', 8, 2)->nullable();
            $table->decimal('amount', 13, 2);
            $table->decimal('input_tax', 13, 2)->default(0);
            $table->timestamps();
            $table->foreign('supplier_credit_id')
                ->references('id')
                ->on('supplier_credits')
                ->onDelete('cascade');
            $table->foreign('product_id')
                ->references('id')
                ->on('products');
        });
        Schema::create('purc_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->date('date');
            $table->unsignedBigInteger('product_id');
            $table->float('quantity', 8, 2)->nullable();
            $table->decimal('amount', 13, 2);
            $table->timestamps();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->foreign('product_id')
                ->references('id')
                ->on('products');
            $table->morphs('returnablepurc');
            $table->morphs('purchasable');
        });
        Schema::create('inventory_qty_adjs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->date('date');
            $table->unsignedBigInteger('number');
            $table->unsignedBigInteger('account_id');
            $table->unique(['company_id', 'number'], 'my_unique_ref');
            $table->timestamps();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->foreign('account_id')
                ->references('id')
                ->on('accounts');
        });
        Schema::create('inventory_qty_adj_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventory_qty_adj_id');
            $table->unsignedBigInteger('product_id');
            $table->text('description')->nullable();
            $table->float('change_in_qty', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('inventory_qty_adj_id')
                ->references('id')
                ->on('inventory_qty_adjs')
                ->onDelete('cascade');
            $table->foreign('product_id')
                ->references('id')
                ->on('products');
        });
        Schema::create('cash_receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('account_id');
            $table->date('date');
            $table->unsignedBigInteger('number');
            $table->text('memo')->nullable();
            $table->unsignedBigInteger('cashback_account_id');
            $table->text('cashback_memo')->nullable();
            $table->decimal('cashback_amount', 13, 2);
            $table->unique(['company_id', 'number'], 'my_unique_ref');
            $table->timestamps();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->foreign('account_id')
                ->references('id')
                ->on('accounts');
            $table->foreign('cashback_account_id')
                ->references('id')
                ->on('accounts');
        });
        Schema::create('cash_receipt_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cash_receipt_id');
            $table->unsignedBigInteger('subsidiary_ledger_id');
            $table->unsignedBigInteger('account_id');
            $table->text('description')->nullable();
            $table->decimal('amount', 13, 2);
            $table->decimal('output_tax', 13, 2)->default(0);
            $table->timestamps();
            $table->foreign('cash_receipt_id')
                ->references('id')
                ->on('cash_receipts')
                ->onDelete('cascade');
            $table->foreign('subsidiary_ledger_id')
                ->references('id')
                ->on('subsidiary_ledgers');
            $table->foreign('account_id')
                ->references('id')
                ->on('accounts');
        });
    }

    protected function createTable(string $tableName): void
    {
        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('customer_id');
            $table->date('date');
            $table->unsignedBigInteger('number');
            $table->unique(['company_id', 'number'], 'my_unique_ref');
            $table->unsignedBigInteger('account_id');
            $table->timestamps();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->foreign('customer_id')
                ->references('id')
                ->on('customers');
            $table->foreign('account_id')
                ->references('id')
                ->on('accounts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
