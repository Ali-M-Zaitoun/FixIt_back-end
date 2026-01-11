<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&family=Inter:wght@400;600&display=swap');

        :root {
            --primary: #1e40af;
            --secondary: #64748b;
            --border: #e2e8f0;
            --bg-light: #f8fafc;
        }

        body {
            font-family: 'Cairo', 'Inter', sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid var(--primary);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .section-label {
            display: flex;
            justify-content: space-between;
            background: var(--bg-light);
            padding: 8px 15px;
            border-right: 4px solid var(--primary);
            margin-top: 20px;
        }

        .label-ar {
            font-weight: 700;
            color: var(--primary);
            font-size: 13px;
        }

        .label-en {
            font-weight: 600;
            color: var(--secondary);
            font-size: 11px;
            font-family: 'Inter';
            text-transform: uppercase;
        }

        .value-row-split {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            border: 1px solid var(--border);
            border-top: none;
            background: #fff;
        }

        .val-ar {
            text-align: right;
            font-weight: 600;
            flex: 1;
        }

        .val-en {
            text-align: left;
            direction: ltr;
            font-family: 'Inter';
            font-size: 14px;
            color: var(--secondary);
            flex: 1;
        }

        .value-full {
            padding: 15px;
            border: 1px solid var(--border);
            border-top: none;
            background: #fff;
            line-height: 1.8;
            font-size: 15px;
        }

        .status-pill {
            padding: 4px 12px;
            background: var(--primary);
            color: white;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <div style="text-align: right;">
            <h1 style="margin:0; font-size: 20px;">تقرير شكوى رسمي</h1>
            <span style="font-size: 12px; color: var(--secondary);">{{ now()->format('Y/m/d') }}</span>
        </div>
        <div style="text-align: left;" dir="ltr">
            <h1 style="margin:0; font-size: 20px;">Official Complaint Report</h1>
            <span style="font-size: 12px; color: var(--secondary);">#{{ $complaint->reference_number }}</span>
        </div>
    </div>

    <div class="section-label"><span class="label-ar">الوزارة</span><span class="label-en">Ministry</span></div>
    <div class="value-row-split">
        <div class="val-ar">{{ $ministryAr }}</div>
        <div class="val-en">{{ $ministryEn }}</div>
    </div>

    @if($branchAr)
    <div class="section-label"><span class="label-ar">الفرع</span><span class="label-en">Branch</span></div>
    <div class="value-row-split">
        <div class="val-ar">{{ $branchAr }}</div>
        <div class="val-en">{{ $branchEn }}</div>
    </div>
    @endif

    <div class="section-label"><span class="label-ar">رقم المرجع والحالة</span><span class="label-en">Reference & Status</span></div>
    <div class="value-row-split">
        <div class="val-ar">المرجع: #{{ $complaint->reference_number }}</div>
        <div class="status-pill">{{ strtoupper($complaint->status) }}</div>
        <div class="val-en">Ref: #{{ $complaint->reference_number }}</div>
    </div>

    <div class="section-label"><span class="label-ar">نوع الشكوى</span><span class="label-en">Complaint Type</span></div>
    <div class="value-full {{ $complaint->isArabic('type') ? 'val-ar' : 'val-en' }}">
        {{ $complaint->type }}
    </div>

    <div class="section-label"><span class="label-ar">محتوى الشكوى</span><span class="label-en">Complaint Content</span></div>
    <div class="value-full {{ $complaint->isArabic('description') ? 'val-ar' : 'val-en' }}">
        {{ $complaint->description }}
    </div>

    @if($complaint->notes)
    <div class="section-label"><span class="label-ar">ملاحظات الإدارة</span><span class="label-en">Management Notes</span></div>
    <div class="value-full" style="background-color: #fefce8;">{{ $complaint->notes }}</div>
    @endif

    @if($complaint->media && $complaint->media->count() > 0)
    <div class="section-label"><span class="label-ar">المرفقات</span><span class="label-en">Attachments</span></div>
    <div class="value-full">
        @foreach($complaint->media as $media)
        📎 {{ $media->path }} {{ !$loop->last ? '|' : '' }}
        @endforeach
    </div>
    @endif

    <div style="margin-top: 60px; text-align: center; font-size: 11px; color: var(--secondary); border-top: 1px solid var(--border); padding-top: 15px;">
        مستند إلكتروني معتمد - لا يتطلب ختماً يدوياً<br>
        Certified Electronic Document - No Manual Stamp Required
    </div>
</body>

</html>