<?php

namespace Tests\Helpers;

use Carbon\Carbon;

class MockEmailDataHelper
{
    /**
     * Generate mock email data for different banks
     */
    public static function generateBankEmails(): array
    {
        return [
            'sbi_debit' => self::generateSBIDebitEmail(),
            'sbi_credit' => self::generateSBICreditEmail(),
            'hdfc_debit' => self::generateHDFCDebitEmail(),
            'hdfc_credit' => self::generateHDFCCreditEmail(),
            'icici_debit' => self::generateICICIDebitEmail(),
            'icici_credit' => self::generateICICICreditEmail(),
            'axis_debit' => self::generateAxisDebitEmail(),
            'axis_credit' => self::generateAxisCreditEmail(),
        ];
    }

    /**
     * Generate SBI debit transaction email
     */
    public static function generateSBIDebitEmail(float $amount = 2500.00, string $merchant = 'AMAZON'): array
    {
        $date = Carbon::now()->format('d-M-Y');
        $time = Carbon::now()->format('H:i:s');
        $refNo = 'TXN' . rand(100000000, 999999999);
        $balance = rand(10000, 100000);

        return [
            'message_id' => 'sbi_debit_' . uniqid(),
            'subject' => 'SBI Account Transaction Alert',
            'sender' => 'sbi.alerts@sbi.co.in',
            'raw_content' => "Dear Customer, Rs. " . number_format($amount, 2) . " has been debited from your account XXXXXX1234 on {$date} at {$time} for payment to {$merchant}. Available balance: Rs. " . number_format($balance, 2) . ". Ref: {$refNo}. For any dispute, please contact customer care.",
            'received_date' => Carbon::now()->toDateTimeString()
        ];
    }

    /**
     * Generate SBI credit transaction email
     */
    public static function generateSBICreditEmail(float $amount = 50000.00, string $description = 'Salary Credit'): array
    {
        $date = Carbon::now()->format('d-M-Y');
        $time = Carbon::now()->format('H:i:s');
        $refNo = 'SAL' . rand(100000000, 999999999);
        $balance = rand(50000, 200000);

        return [
            'message_id' => 'sbi_credit_' . uniqid(),
            'subject' => 'SBI Account Credit Alert',
            'sender' => 'sbi.alerts@sbi.co.in',
            'raw_content' => "Dear Customer, Rs. " . number_format($amount, 2) . " has been credited to your account XXXXXX1234 on {$date} at {$time}. Transaction: {$description}. Available balance: Rs. " . number_format($balance, 2) . ". Ref: {$refNo}.",
            'received_date' => Carbon::now()->toDateTimeString()
        ];
    }

    /**
     * Generate HDFC debit transaction email
     */
    public static function generateHDFCDebitEmail(float $amount = 1500.00, string $merchant = 'SWIGGY'): array
    {
        $date = Carbon::now()->format('d/m/Y');
        $time = Carbon::now()->format('H:i');
        $refNo = 'UPI' . rand(100000000000, 999999999999);
        $balance = rand(15000, 80000);

        return [
            'message_id' => 'hdfc_debit_' . uniqid(),
            'subject' => 'HDFC Bank Transaction Alert',
            'sender' => 'alerts@hdfcbank.com',
            'raw_content' => "Dear Customer, INR " . number_format($amount, 2) . " has been debited from your HDFC Bank Account XX1234 on {$date} {$time} for UPI payment to {$merchant}. Available Balance: INR " . number_format($balance, 2) . ". UPI Ref No: {$refNo}. Thank you for banking with us.",
            'received_date' => Carbon::now()->toDateTimeString()
        ];
    }

    /**
     * Generate HDFC credit transaction email
     */
    public static function generateHDFCCreditEmail(float $amount = 25000.00, string $description = 'Salary Credit'): array
    {
        $date = Carbon::now()->format('d/m/Y');
        $time = Carbon::now()->format('H:i');
        $refNo = 'CR' . rand(100000000000, 999999999999);
        $balance = rand(40000, 150000);

        return [
            'message_id' => 'hdfc_credit_' . uniqid(),
            'subject' => 'HDFC Bank Credit Alert',
            'sender' => 'alerts@hdfcbank.com',
            'raw_content' => "Dear Customer, INR " . number_format($amount, 2) . " has been credited to your HDFC Bank Account XX1234 on {$date} {$time}. Transaction: {$description}. Available Balance: INR " . number_format($balance, 2) . ". Ref No: {$refNo}.",
            'received_date' => Carbon::now()->toDateTimeString()
        ];
    }

    /**
     * Generate ICICI debit transaction email
     */
    public static function generateICICIDebitEmail(float $amount = 800.00, string $merchant = 'UBER'): array
    {
        $date = Carbon::now()->format('d-M-y');
        $refNo = rand(100000000000, 999999999999);
        $balance = rand(8000, 50000);

        return [
            'message_id' => 'icici_debit_' . uniqid(),
            'subject' => 'ICICI Bank Account Alert',
            'sender' => 'no-reply@icicibank.com',
            'raw_content' => "Dear Customer, Rs " . number_format($amount, 2) . " debited from A/c XX9876 on {$date} for UPI payment to {$merchant}. Ref No: {$refNo}. Available Bal: Rs " . number_format($balance, 2) . ". Download iMobile Pay app for easy banking.",
            'received_date' => Carbon::now()->toDateTimeString()
        ];
    }

    /**
     * Generate ICICI credit transaction email
     */
    public static function generateICICICreditEmail(float $amount = 15000.00, string $description = 'Interest Credit'): array
    {
        $date = Carbon::now()->format('d-M-y');
        $refNo = rand(100000000000, 999999999999);
        $balance = rand(30000, 100000);

        return [
            'message_id' => 'icici_credit_' . uniqid(),
            'subject' => 'ICICI Bank Credit Alert',
            'sender' => 'no-reply@icicibank.com',
            'raw_content' => "Dear Customer, Rs " . number_format($amount, 2) . " credited to A/c XX9876 on {$date}. Transaction: {$description}. Ref No: {$refNo}. Available Bal: Rs " . number_format($balance, 2) . ".",
            'received_date' => Carbon::now()->toDateTimeString()
        ];
    }

    /**
     * Generate Axis Bank debit transaction email
     */
    public static function generateAxisDebitEmail(float $amount = 3200.00, string $merchant = 'FLIPKART'): array
    {
        $date = Carbon::now()->format('d/m/Y');
        $time = Carbon::now()->format('H:i:s');
        $refNo = 'AX' . rand(1000000000, 9999999999);
        $balance = rand(20000, 90000);

        return [
            'message_id' => 'axis_debit_' . uniqid(),
            'subject' => 'Axis Bank Transaction Alert',
            'sender' => 'alerts@axisbank.com',
            'raw_content' => "Dear Customer, Your A/c XX5678 is debited for Rs " . number_format($amount, 2) . " on {$date} {$time} towards payment to {$merchant}. Available balance Rs " . number_format($balance, 2) . ". Txn Ref: {$refNo}. For support call 1860-419-5555.",
            'received_date' => Carbon::now()->toDateTimeString()
        ];
    }

    /**
     * Generate Axis Bank credit transaction email
     */
    public static function generateAxisCreditEmail(float $amount = 35000.00, string $description = 'Salary Credit'): array
    {
        $date = Carbon::now()->format('d/m/Y');
        $time = Carbon::now()->format('H:i:s');
        $refNo = 'AX' . rand(1000000000, 9999999999);
        $balance = rand(60000, 180000);

        return [
            'message_id' => 'axis_credit_' . uniqid(),
            'subject' => 'Axis Bank Credit Alert',
            'sender' => 'alerts@axisbank.com',
            'raw_content' => "Dear Customer, Your A/c XX5678 is credited for Rs " . number_format($amount, 2) . " on {$date} {$time}. Transaction: {$description}. Available balance Rs " . number_format($balance, 2) . ". Txn Ref: {$refNo}.",
            'received_date' => Carbon::now()->toDateTimeString()
        ];
    }

    /**
     * Generate ATM withdrawal email
     */
    public static function generateATMWithdrawalEmail(float $amount = 5000.00, string $location = 'SBI ATM CONNAUGHT PLACE'): array
    {
        $date = Carbon::now()->format('d-M-Y');
        $time = Carbon::now()->format('H:i:s');
        $refNo = 'ATM' . rand(100000000, 999999999);
        $balance = rand(10000, 80000);

        return [
            'message_id' => 'atm_withdrawal_' . uniqid(),
            'subject' => 'ATM Withdrawal Alert',
            'sender' => 'sbi.alerts@sbi.co.in',
            'raw_content' => "Dear Customer, Rs. " . number_format($amount, 2) . " has been withdrawn from your account XXXXXX1234 on {$date} at {$time} from {$location}. Available balance: Rs. " . number_format($balance, 2) . ". Ref: {$refNo}.",
            'received_date' => Carbon::now()->toDateTimeString()
        ];
    }

    /**
     * Generate bill payment email
     */
    public static function generateBillPaymentEmail(float $amount = 1200.00, string $biller = 'ELECTRICITY BOARD'): array
    {
        $date = Carbon::now()->format('d-M-Y');
        $refNo = 'BILL' . rand(100000000, 999999999);
        $balance = rand(15000, 70000);

        return [
            'message_id' => 'bill_payment_' . uniqid(),
            'subject' => 'Bill Payment Confirmation',
            'sender' => 'alerts@hdfcbank.com',
            'raw_content' => "Dear Customer, Your bill payment of INR " . number_format($amount, 2) . " to {$biller} has been processed successfully on {$date}. Available Balance: INR " . number_format($balance, 2) . ". Reference: {$refNo}. Thank you for using HDFC Bank services.",
            'received_date' => Carbon::now()->toDateTimeString()
        ];
    }

    /**
     * Generate non-bank email (should not be processed)
     */
    public static function generateNonBankEmail(): array
    {
        return [
            'message_id' => 'non_bank_' . uniqid(),
            'subject' => 'Weekly Newsletter - Tech Updates',
            'sender' => 'newsletter@techcompany.com',
            'raw_content' => 'Dear Subscriber, Here are this week\'s top technology updates and news. We hope you find them interesting and informative. Best regards, Tech Company Team.',
            'received_date' => Carbon::now()->toDateTimeString()
        ];
    }

    /**
     * Generate malformed bank email (should fail parsing)
     */
    public static function generateMalformedBankEmail(): array
    {
        return [
            'message_id' => 'malformed_' . uniqid(),
            'subject' => 'Bank Transaction Alert',
            'sender' => 'alerts@somebank.com',
            'raw_content' => 'Dear Customer, some transaction happened but we forgot to mention the amount or any useful details. Please contact us for more information.',
            'received_date' => Carbon::now()->toDateTimeString()
        ];
    }

    /**
     * Get all test email scenarios
     */
    public static function getAllTestScenarios(): array
    {
        return [
            'valid_bank_emails' => self::generateBankEmails(),
            'atm_withdrawal' => self::generateATMWithdrawalEmail(),
            'bill_payment' => self::generateBillPaymentEmail(),
            'non_bank_email' => self::generateNonBankEmail(),
            'malformed_email' => self::generateMalformedBankEmail(),
        ];
    }

    /**
     * Generate email data with specific configuration
     */
    public static function generateEmailWithConfig(int $configId, array $overrides = []): array
    {
        $defaultEmail = self::generateSBIDebitEmail();
        $defaultEmail['email_configuration_id'] = $configId;
        
        return array_merge($defaultEmail, $overrides);
    }
}