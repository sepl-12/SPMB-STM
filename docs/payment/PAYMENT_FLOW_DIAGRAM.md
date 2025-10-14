# Payment Flow Diagram - Midtrans Snap Integration

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────┐
│                         PPDB SYSTEM                                 │
│                                                                     │
│  ┌──────────────┐      ┌──────────────┐      ┌──────────────┐    │
│  │ Registration │ ───▶ │   Payment    │ ───▶ │   Success    │    │
│  │  Controller  │      │  Controller  │      │    Page      │    │
│  └──────────────┘      └──────────────┘      └──────────────┘    │
│         │                      │                                   │
│         │                      │                                   │
│         ▼                      ▼                                   │
│  ┌──────────────┐      ┌──────────────┐                          │
│  │   Applicant  │      │   Payment    │                          │
│  │    Model     │      │    Model     │                          │
│  └──────────────┘      └──────────────┘                          │
│         │                      │                                   │
│         │                      ▼                                   │
│         │              ┌──────────────┐                          │
│         │              │  Midtrans    │                          │
│         │              │   Service    │                          │
│         │              └──────────────┘                          │
└─────────────────────────────┼───────────────────────────────────┘
                              │
                              │ API Call
                              ▼
                    ┌──────────────────┐
                    │  Midtrans API    │
                    │   (Snap Token)   │
                    └──────────────────┘
```

## Detailed Payment Flow

### 1. Registration Phase

```
User
  │
  ├─▶ Fill Registration Form
  │
  ├─▶ Submit Form
  │
  ▼
RegistrationController::saveStep()
  │
  ├─▶ Create Applicant Record
  │     └─▶ applicant_id, registration_number, applicant_full_name
  │
  ├─▶ Create Submission Record
  │
  └─▶ Redirect to Success Page
        │
        └─▶ /daftar/success/{registration_number}
              │
              └─▶ Button: "Lanjutkan Pembayaran"
```

### 2. Payment Initialization

```
User clicks "Lanjutkan Pembayaran"
  │
  ▼
PaymentController::show($registration_number)
  │
  ├─▶ Fetch Applicant with Wave
  │
  ├─▶ Check if already paid
  │     └─▶ Yes: Redirect to success
  │
  ├─▶ Check existing pending payment
  │     ├─▶ Yes: Use existing Snap Token
  │     └─▶ No: Create new transaction
  │
  ▼
MidtransService::createTransaction($applicant)
  │
  ├─▶ Generate Order ID (ORD-{registration_number}-{timestamp})
  │
  ├─▶ Prepare Transaction Parameters
  │     ├─▶ transaction_details (order_id, gross_amount)
  │     ├─▶ customer_details (name, email, phone)
  │     ├─▶ item_details (item name, price)
  │     └─▶ callbacks (finish URL)
  │
  ├─▶ Call Midtrans Snap API
  │     └─▶ Snap::getSnapToken($params)
  │
  ├─▶ Create Payment Record
  │     └─▶ status: PENDING
  │
  └─▶ Return Snap Token
        │
        ▼
Show Payment Page with Snap Token
```

### 3. User Payment Process

```
Payment Page Loaded
  │
  ├─▶ Display Applicant Info
  ├─▶ Display Amount
  └─▶ Button: "Bayar Sekarang"
        │
        │ User clicks
        ▼
JavaScript: snap.pay(snapToken)
  │
  ▼
Midtrans Snap Popup Opens
  │
  ├─▶ User selects payment method
  │     ├─▶ Bank Transfer
  │     ├─▶ E-Wallet (GoPay, OVO, DANA)
  │     ├─▶ Credit Card
  │     ├─▶ Virtual Account
  │     └─▶ QRIS
  │
  ├─▶ User completes payment
  │
  └─▶ Snap Callback
        ├─▶ onSuccess()  ─────┐
        ├─▶ onPending()  ─────┤
        ├─▶ onError()    ─────┤
        └─▶ onClose()         │
                              │
                              ▼
        Redirect to: /pembayaran/status/{registration_number}
```

### 4. Webhook Notification (Background)

```
Midtrans Payment Gateway
  │
  │ After payment processed
  │
  ├─▶ Send HTTP POST to Webhook
  │     └─▶ URL: /pembayaran/notification
  │
  ▼
PaymentController::notification()
  │
  ├─▶ Receive notification data
  │     ├─▶ order_id
  │     ├─▶ transaction_status
  │     ├─▶ payment_type
  │     ├─▶ gross_amount
  │     └─▶ fraud_status
  │
  ├─▶ Log notification
  │
  ▼
MidtransService::handleNotification($notification)
  │
  ├─▶ Find Payment by order_id
  │
  ├─▶ Determine payment status
  │     ├─▶ settlement ─────▶ PAID
  │     ├─▶ capture ────────▶ PAID (if fraud_status = accept)
  │     ├─▶ pending ────────▶ PENDING
  │     ├─▶ cancel/deny/expire ─▶ FAILED
  │     └─▶ others ─────────▶ (as is)
  │
  ├─▶ Update Payment Record
  │     ├─▶ payment_status_name
  │     ├─▶ payment_method_name
  │     ├─▶ status_updated_datetime
  │     └─▶ gateway_payload_json (append notification)
  │
  ├─▶ Update Applicant status
  │     ├─▶ PAID ────────▶ payment_status = 'paid'
  │     └─▶ FAILED ──────▶ payment_status = 'unpaid'
  │
  └─▶ Return JSON response
```

### 5. Status Checking

```
Payment Status Page
  │
  ├─▶ Display current status
  │
  ├─▶ Auto-refresh every 30 seconds (if pending)
  │     │
  │     └─▶ AJAX call to /pembayaran/check-status
  │           │
  │           ▼
  │     PaymentController::checkStatus()
  │           │
  │           ├─▶ MidtransService::checkTransactionStatus()
  │           │     │
  │           │     └─▶ Call Midtrans API
  │           │
  │           └─▶ Return JSON with status
  │
  └─▶ Status Display
        ├─▶ PAID ─────────▶ Success Icon (Green)
        ├─▶ PENDING ──────▶ Clock Icon (Yellow)
        └─▶ FAILED ───────▶ Error Icon (Red)
```

### 6. Complete Flow Summary

```
┌─────────────┐
│   User      │
└──────┬──────┘
       │
       ├─▶ 1. Register
       │      └─▶ Create Applicant
       │
       ├─▶ 2. Click "Bayar"
       │      ├─▶ Create Transaction
       │      └─▶ Get Snap Token
       │
       ├─▶ 3. Open Snap Popup
       │      └─▶ Select Payment Method
       │
       ├─▶ 4. Complete Payment
       │      └─▶ (External - Bank/E-wallet)
       │
       └─▶ 5. View Status
              └─▶ Auto-updated via Webhook
                    │
                    ▼
            ┌──────────────┐
            │   Midtrans   │
            │   Webhook    │
            └──────────────┘
                    │
                    ▼
            Update Database
                    │
                    ├─▶ Payment Status
                    └─▶ Applicant Status
```

## Data Flow

### Request Flow

```
Client ─────▶ PaymentController ─────▶ MidtransService ─────▶ Midtrans API
   │               │                         │                      │
   │               │                         │                      │
   │               └─────────────────────────┴──────────────────────┤
   │                                                                 │
   └─────────────────────────────────────────────────────────────────┘
                          ◀─── Snap Token
```

### Notification Flow

```
Midtrans API ─────▶ Webhook Endpoint ─────▶ PaymentController
                         │                         │
                         │                         ▼
                         │                  MidtransService
                         │                         │
                         │                         ▼
                         │                  Update Database
                         │                    ├─▶ payments
                         │                    └─▶ applicants
                         │
                         └─────────────────────── OK Response
```

## State Diagram

```
┌─────────────┐
│  NOT PAID   │
└──────┬──────┘
       │
       │ User clicks "Bayar"
       ▼
┌─────────────┐
│   PENDING   │◀──────────────────────────────────┐
└──────┬──────┘                                    │
       │                                           │
       ├─▶ Payment Completed ────▶ PAID/SETTLEMENT│
       │                                           │
       ├─▶ Payment Failed ────────▶ FAILED        │
       │                                           │
       └─▶ Payment Expired ───────▶ EXPIRED ──────┘
                                    (Create New Transaction)
```

## Security Flow

```
┌──────────────────────────────────────────────────────────┐
│                    Security Measures                     │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  1. CSRF Protection                                      │
│     └─▶ Exempted for webhook endpoint                   │
│         (Midtrans uses signature verification)           │
│                                                          │
│  2. Signature Verification                               │
│     └─▶ Midtrans SDK validates signature                │
│         automatically                                     │
│                                                          │
│  3. HTTPS (Production)                                   │
│     └─▶ All communications encrypted                    │
│                                                          │
│  4. Amount Validation                                    │
│     └─▶ Compare notification amount with DB             │
│                                                          │
│  5. Order ID Validation                                  │
│     └─▶ Check order exists in database                  │
│                                                          │
└──────────────────────────────────────────────────────────┘
```

## Error Handling Flow

```
Try Payment Creation
  │
  ├─▶ Success
  │     └─▶ Show payment page
  │
  └─▶ Error
        ├─▶ Network Error
        │     └─▶ Show error message
        │         └─▶ Retry button
        │
        ├─▶ Invalid Config
        │     └─▶ Log error
        │         └─▶ Show generic error
        │
        └─▶ API Error
              └─▶ Log error details
                  └─▶ Show user-friendly message
```

## Database State Changes

```
Registration Complete
  │
  ▼
┌─────────────────────────────────────┐
│ applicants table                    │
│ - payment_status = 'unpaid'         │
└─────────────────────────────────────┘
  │
  │ Create Transaction
  ▼
┌─────────────────────────────────────┐
│ payments table                      │
│ - payment_status_name = 'PENDING'   │
└─────────────────────────────────────┘
  │
  │ Webhook: Settlement
  ▼
┌─────────────────────────────────────┐
│ payments table                      │
│ - payment_status_name = 'PAID'      │
│ - status_updated_datetime = now()   │
└─────────────────────────────────────┘
  │
  ▼
┌─────────────────────────────────────┐
│ applicants table                    │
│ - payment_status = 'paid'           │
└─────────────────────────────────────┘
```

---

## Legend

```
─────▶   Flow direction
│        Sequential steps
├─▶      Branch/Option
└─▶      Final step in branch
◀───     Return/Response
```

---

**This diagram represents the complete payment flow of the Midtrans Snap integration in the PPDB system.**
