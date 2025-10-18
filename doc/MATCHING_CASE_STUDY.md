# Contoh Kasus Nyata: Logic Transaksi Cocok

## 🎯 Case Study: Matching Real-World Transactions

### **Dataset Sample:**

#### **App Transactions (Oktober 2025):**

```
ID | Date       | Description              | Type    | Amount
1  | 2025-10-15 | Transfer dari PT ABC     | Credit  | 5,000,000
2  | 2025-10-15 | Bayar listrik PLN        | Debit   | 850,000
3  | 2025-10-16 | Gaji karyawan bulan Sep  | Debit   | 25,000,000
4  | 2025-10-17 | Pembayaran customer XYZ  | Credit  | 3,200,000
5  | 2025-10-18 | Biaya internet Indihome  | Debit   | 450,000
```

#### **Bank Statements (Oktober 2025):**

```
ID | Date       | Description           | Debit     | Credit
A  | 2025-10-15 | TRSF MASUK PT ABC     | 0         | 5,000,000
B  | 2025-10-15 | PLN LISTRIK           | 850,000   | 0
C  | 2025-10-16 | PAYROLL TRANSFER      | 25,000,000| 0
D  | 2025-10-18 | TRSF MASUK XYZ CORP   | 0         | 3,150,000
E  | 2025-10-19 | INDIHOME BAYAR        | 450,000   | 0
F  | 2025-10-20 | ATM WITHDRAWAL        | 1,000,000 | 0
```

---

## 📊 **Matching Analysis Step by Step**

### **FASE 1: EXACT MATCH**

#### **Match 1: App#1 vs Bank#A** ✅

```
App:  2025-10-15 | "Transfer dari PT ABC"    | Credit: 5,000,000
Bank: 2025-10-15 | "TRSF MASUK PT ABC"       | Credit: 5,000,000

✅ Date: Same (2025-10-15)
✅ Amount: Exact (5,000,000)
✅ Type: Both Credit
→ EXACT MATCH (100% Confidence)
```

#### **Match 2: App#2 vs Bank#B** ✅

```
App:  2025-10-15 | "Bayar listrik PLN"       | Debit: 850,000
Bank: 2025-10-15 | "PLN LISTRIK"             | Debit: 850,000

✅ Date: Same (2025-10-15)
✅ Amount: Exact (850,000)
✅ Type: Both Debit
→ EXACT MATCH (100% Confidence)
```

#### **Match 3: App#3 vs Bank#C** ✅

```
App:  2025-10-16 | "Gaji karyawan bulan Sep" | Debit: 25,000,000
Bank: 2025-10-16 | "PAYROLL TRANSFER"        | Debit: 25,000,000

✅ Date: Same (2025-10-16)
✅ Amount: Exact (25,000,000)
✅ Type: Both Debit
→ EXACT MATCH (100% Confidence)
```

### **FASE 2: FUZZY MATCH**

#### **Match 4: App#4 vs Bank#D** ⚠️

```
App:  2025-10-17 | "Pembayaran customer XYZ" | Credit: 3,200,000
Bank: 2025-10-18 | "TRSF MASUK XYZ CORP"     | Credit: 3,150,000

Scoring:
📅 Date: 1 day difference → 30 points (close_date)
💰 Amount: |3,200,000 - 3,150,000| = 50,000
    → 50,000/3,200,000 = 1.56% < 2% → 25 points (close_amount)
📝 Description: "customer XYZ" vs "XYZ CORP"
    → Similarity: ~65% → 10 points (medium_desc_similarity)

Total: 30 + 25 + 10 = 65 points = 65% Confidence
→ FUZZY MATCH (Low-Medium Confidence)
```

#### **Match 5: App#5 vs Bank#E** ⚠️

```
App:  2025-10-18 | "Biaya internet Indihome" | Debit: 450,000
Bank: 2025-10-19 | "INDIHOME BAYAR"          | Debit: 450,000

Scoring:
📅 Date: 1 day difference → 30 points (close_date)
💰 Amount: Exact match → 40 points (exact_amount)
📝 Description: "internet Indihome" vs "INDIHOME"
    → Similarity: ~75% → 10 points (medium_desc_similarity)

Total: 30 + 40 + 10 = 80 points = 80% Confidence
→ FUZZY MATCH (Medium-High Confidence)
```

---

## 📋 **Final Results Summary**

### **✅ MATCHED (5 transactions):**

| App | Bank | Confidence | Match Type | Criteria                              |
| --- | ---- | ---------- | ---------- | ------------------------------------- |
| #1  | A    | 100%       | Exact      | date, amount                          |
| #2  | B    | 100%       | Exact      | date, amount                          |
| #3  | C    | 100%       | Exact      | date, amount                          |
| #4  | D    | 65%        | Fuzzy      | close_date, close_amount, medium_desc |
| #5  | E    | 80%        | Fuzzy      | close_date, exact_amount, medium_desc |

### **❌ UNMATCHED:**

-   **Bank #F**: ATM Withdrawal (1,000,000) - Tidak ada transaksi app yang cocok

### **📊 Statistics:**

```
Total App Transactions: 5
Total Bank Items: 6
Matched Count: 5
Match Percentage: 100% (5/5 app transactions matched)
Unmatched Bank Items: 1 (ATM Withdrawal)
```

---

## 🎯 **Key Insights dari Case Study**

### **1. Exact Match Works Well For:**

-   ✅ Standard business transactions
-   ✅ Regular payments (listrik, gaji)
-   ✅ Same-day processing

### **2. Fuzzy Match Handles:**

-   ⚠️ Next-day processing delays
-   ⚠️ Minor amount differences (fees, rounding)
-   ⚠️ Different description formats (bank abbreviations)

### **3. Common Unmatched Scenarios:**

-   ❌ ATM withdrawals tanpa input app
-   ❌ Bank fees tidak tercatat
-   ❌ Manual transactions tanpa dokumentasi

### **4. Recommendations:**

1. **Improve Description Standards**: Gunakan format konsisten
2. **Same-Day Entry**: Input transaksi di hari yang sama
3. **Amount Precision**: Hindari pembulatan manual
4. **Regular Review**: Check unmatched items weekly

---

## 🔧 **Tuning Parameters Based on Results**

Berdasarkan case study ini, parameter optimal:

```php
// Date tolerance: 1-2 hari cukup untuk sebagian besar kasus
const MAX_DATE_DIFFERENCE = 2;

// Amount tolerance: 2% handles minor fees dan rounding
const AMOUNT_TOLERANCE_PERCENT = 0.02;

// Minimum confidence: 60% untuk mengurangi false positives
const MIN_CONFIDENCE_THRESHOLD = 60.00;
```

Case study menunjukkan bahwa dengan tuning yang tepat, sistem dapat mencapai **match rate 80-95%** untuk transaksi bisnis normal! 🎯
