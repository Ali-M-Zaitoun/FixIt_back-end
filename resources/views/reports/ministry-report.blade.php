<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <style>
        /* بديل Tailwind البسيط للـ PDF */
        body {
            font-family: 'Arial', sans-serif;
            direction: rtl;
            background-color: white;
            padding: 20px;
            color: #1e293b;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }

        .header {
            padding: 30px;
            border-bottom: 4px solid #0f172a;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .title {
            font-size: 24px;
            font-weight: 900;
            color: #0f172a;
            margin: 0;
        }

        .subtitle {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
        }

        .stats-box {
            margin: 30px;
            text-align: center;
            background-color: #0f172a;
            color: white;
            padding: 30px;
            border-radius: 12px;
        }

        .stats-label {
            color: #60a5fa;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .stats-value {
            font-size: 48px;
            font-weight: 900;
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: right;
        }

        th {
            padding: 12px;
            border-bottom: 2px solid #e2e8f0;
            font-size: 10px;
            color: #94a3b8;
            text-transform: uppercase;
        }

        td {
            padding: 16px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
        }

        .branch-en {
            font-size: 10px;
            color: #94a3b8;
            text-transform: uppercase;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .bg-blue-50 {
            background-color: #eff6ff;
            color: #2563eb;
        }

        .footer {
            padding: 15px;
            background-color: #f8fafc;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            font-size: 10px;
            color: #94a3b8;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div>
                <h1 class="title">ملخص الوزارة التنفيذي</h1>
                <div class="subtitle">Ministry Executive Summary</div>
            </div>
            <div style="color: #94a3b8; font-size: 12px; font-weight: bold;">
                {{ $ministryAr }} / {{ $ministryEn }}
            </div>
        </div>

        <div class="stats-box">
            <p class="stats-label">إجمالي الشكاوى بالوزارة / Total Ministry Complaints</p>
            <h1 class="stats-value">{{ $total }}</h1>
        </div>

        <div style="padding: 0 30px 30px 30px;">
            <table>
                <thead>
                    <tr>
                        <th>الفرع / Branch</th>
                        <th class="text-center">جديد / New</th>
                        <th class="text-center">قيد التنفيذ / Progress</th>
                        <th class="text-center">مكتمل / Resolved</th>
                        <th class="text-center">مرفوض / Rejected</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($branches as $b)
                    <tr>
                        <td>
                            <div class="font-bold">{{ $b->name_ar }}</div>
                            <div class="branch-en">{{ $b->name_en }}</div>
                        </td>
                        <td class="text-center font-bold bg-blue-50">{{ $b->new }}</td>
                        <td class="text-center font-bold" style="color: #ca8a04;">{{ $b->progress }}</td>
                        <td class="text-center font-bold" style="color: #16a34a;">{{ $b->resolved }}</td>
                        <td class="text-center font-bold" style="color: #dc2626;">{{ $b->rejected }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="footer">CONFIDENTIAL - للموظفين المصرح لهم فقط</div>
    </div>
</body>

</html>