# Flowchart Logic Reconciliation Matching

## 📊 Visual Flow Algoritma Matching

```
START: Bank Reconciliation
         |
         v
┌─────────────────────────────┐
│   Load App Transactions     │
│   Load Bank Statements      │
└─────────────┬───────────────┘
              │
              v
┌─────────────────────────────┐
│     FASE 1: EXACT MATCH     │
│                             │
│  For each App Transaction:  │
│  ┌─ Same Date?             │
│  ├─ Same Amount?           │
│  └─ Same Type (Debit/Credit)? │
└─────────────┬───────────────┘
              │
         ┌────v────┐
         │ Match?  │
         └────┬────┘
              │
        ┌─────┴─────┐
       Yes          No
        │            │
        v            v
┌─────────────┐   ┌─────────────┐
│   MATCHED   │   │  Continue   │
│ Confidence: │   │ to Phase 2  │
│    100%     │   │             │
└─────────────┘   └──────┬──────┘
                         │
                         v
              ┌─────────────────────────────┐
              │    FASE 2: FUZZY MATCH      │
              │                             │
              │  Calculate Scoring:         │
              │  ┌─ Date Proximity (40pts) │
              │  ├─ Amount Match (40pts)   │
              │  └─ Description Sim (20pts)│
              └─────────────┬───────────────┘
                           │
                      ┌────v────┐
                      │Score ≥  │
                      │  50%?   │
                      └────┬────┘
                           │
                   ┌───────┴───────┐
                  Yes             No
                   │               │
                   v               v
            ┌─────────────┐  ┌─────────────┐
            │   MATCHED   │  │ UNMATCHED   │
            │ Confidence: │  │             │
            │  50-99%     │  │             │
            └─────────────┘  └─────────────┘
                   │               │
                   v               v
            ┌─────────────┐  ┌─────────────┐
            │Mark as Match│  │Add to       │
            │in Database  │  │Unmatched    │
            │             │  │List         │
            └─────────────┘  └─────────────┘
                   │               │
                   └───────┬───────┘
                           │
                           v
                  ┌─────────────────┐
                  │  Generate       │
                  │  Statistics &   │
                  │  Export Results │
                  └─────────────────┘
                           │
                           v
                        END
```

## 🎯 Decision Points Detail

### 1. **Exact Match Criteria**

```
Date Check: app_date === bank_date
  ↓
Amount Check: |app_amount - bank_amount| < 0.01
  ↓
Type Check: (app_credit > 0) === (bank_credit > 0)
```

### 2. **Fuzzy Match Scoring**

```
Date Score:
├─ 0 days diff → 40 points
├─ 1 day diff  → 30 points
├─ 2-3 days    → 15 points
└─ >3 days     → SKIP

Amount Score:
├─ Exact       → 40 points
├─ Within 2%   → 25 points
└─ >2% diff    → SKIP

Description Score:
├─ >80% similar → 20 points
├─ 60-80%      → 10 points
├─ 40-60%      → 5 points
└─ <40%        → 0 points
```

### 3. **Confidence Classification**

```
Total Score → Confidence Level
├─ 100%     → EXACT MATCH
├─ 85-99%   → HIGH CONFIDENCE
├─ 70-84%   → MEDIUM CONFIDENCE
├─ 50-69%   → LOW CONFIDENCE
└─ <50%     → NO MATCH
```

## 📈 Performance Optimization

### **Algorithm Complexity:**

-   **Phase 1**: O(n × m) - where n = app transactions, m = bank items
-   **Phase 2**: O(k × m) - where k = unmatched app transactions
-   **Overall**: O(n × m) + string similarity calculations

### **Optimization Strategies:**

1. **Early Termination**: Skip if date > 3 days or amount > 2%
2. **Indexing**: Pre-sort by date and amount ranges
3. **Caching**: Cache similarity calculations for repeated descriptions
4. **Batch Processing**: Process in chunks for large datasets
