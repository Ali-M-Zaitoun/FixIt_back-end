<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }

        @media print {
            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>

<body class="bg-white p-4">
    <div class="max-w-5xl mx-auto border border-gray-200 shadow-sm rounded-lg overflow-hidden">
        <div class="bg-slate-800 p-6 text-white flex justify-between items-start">
            <div class="text-right">
                <h1 class="text-xl font-bold uppercase">تقرير نشاط الفرع</h1>
                <p class="text-slate-400 text-sm">Branch Activity Report</p>
            </div>
            <div class="text-left">
                <p class="text-blue-400 font-bold text-lg">{{ $ministryAr }}</p>
                <p class="text-slate-500 text-xs uppercase">{{ $ministryEn }}</p>
            </div>
        </div>

        <div class="p-8">
            <div class="grid grid-cols-2 gap-8 mb-8 bg-slate-50 p-4 rounded border-b-2 border-slate-200">
                <div class="text-right">
                    <span class="text-[10px] font-black text-slate-400 uppercase">الفرع والموقع / Branch & Location</span>
                    <p class="text-lg font-bold text-slate-700">{{ $branchAr }} - {{ $branch->governorate->translation('ar')->name }}</p>
                    <p class="text-xs text-slate-500">{{ $branchEn }} - {{ $branch->governorate->translation('en')->name }}</p>
                </div>
                <div class="text-left border-l border-slate-300 pl-6">
                    <span class="text-[10px] font-black text-slate-400 uppercase">المسؤول / Responsible</span>
                    <p class="text-lg font-bold text-slate-700">{{ $manager }}</p>
                </div>
            </div>

            <div class="grid grid-cols-5 gap-2 mb-10 text-center">
                <div class="bg-slate-100 p-2 rounded border-t-4 border-slate-400">
                    <span class="block text-[10px] font-bold text-slate-500">الإجمالي / Total</span>
                    <p class="text-xl font-black">{{ $total }}</p>
                </div>
                <div class="bg-blue-50 p-2 rounded border-t-4 border-blue-400">
                    <span class="block text-[10px] font-bold text-blue-600">جديد / New</span>
                    <p class="text-xl font-black text-blue-700">{{ $statuses['new_count'] }}</p>
                </div>
                <div class="bg-yellow-50 p-2 rounded border-t-4 border-yellow-400">
                    <span class="block text-[10px] font-bold text-yellow-600">قيد التنفيذ / In-Progress</span>
                    <p class="text-xl font-black text-yellow-700">{{ $statuses['progress_count'] }}</p>
                </div>
                <div class="bg-green-50 p-2 rounded border-t-4 border-green-400">
                    <span class="block text-[10px] font-bold text-green-600">مكتمل / Resolved</span>
                    <p class="text-xl font-black text-green-700">{{ $statuses['resolved_count'] }}</p>
                </div>
                <div class="bg-red-50 p-2 rounded border-t-4 border-red-400">
                    <span class="block text-[10px] font-bold text-red-600">مرفوض / Rejected</span>
                    <p class="text-xl font-black text-red-700">{{ $statuses['rejected_count'] }}</p>
                </div>
            </div>

            <div class="mb-8 border-r-4 border-blue-600 pr-4">
                <p class="text-sm font-bold text-slate-500">عدد الموظفين / Employees Count</p>
                <p class="text-2xl font-black">{{ $branch->employees->count() }}</p>
            </div>

            <h3 class="bg-slate-100 p-2 text-sm font-bold mb-4 flex justify-between">
                <span>النشاط الأخير بالفرع</span>
                <span class="text-xs font-normal">Recent Branch Activity</span>
            </h3>
            <table class="w-full text-right border">
                <thead>
                    <tr class="bg-gray-50 text-[10px] uppercase border-b">
                        <th class="p-2 border-l">التاريخ / Date</th>
                        <th class="p-2 border-l">النشاط / Activity</th>
                        <th class="p-2">الحالة / Status</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @foreach($activities as $activity)
                    @php
                    $status = $activity->getExtraProperty('attributes.status');
                    $event = $activity->event;

                    $isNew = ($event === 'created');
                    $isResolved = ($status === 'resolved');
                    $isRejected = ($status === 'rejected');

                    if (!$isNew && !$isResolved && !$isRejected) {
                    continue;
                    }

                    $config = match(true) {
                    $isNew => ['color' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'ar' => 'جديد', 'en' => 'New', 'border' => 'border-emerald-400'],
                    $isResolved => ['color' => 'text-blue-700', 'bg' => 'bg-blue-50', 'ar' => 'تم الحل', 'en' => 'Resolved', 'border' => 'border-blue-400'],
                    $isRejected => ['color' => 'text-red-700', 'bg' => 'bg-red-50', 'ar' => 'مرفوض', 'en' => 'Rejected', 'border' => 'border-red-400'],
                    default => ['color' => 'text-slate-600', 'bg' => 'bg-slate-50', 'ar' => 'تحديث', 'en' => 'Updated', 'border' => 'border-slate-400'],
                    };

                    $refNum = $activity->getExtraProperty('attributes.reference_number') ?? '---';

                    // Logic for "By": Show Citizen for new, Employee for others
                    $personName = $activity->causer
                    ? ($activity->causer->first_name . ' ' . $activity->causer->last_name)
                    : 'النظام / System';

                    $roleLabelAr = $isNew ? 'المواطن' : 'الموظف';
                    $roleLabelEn = $isNew ? 'Citizen' : 'Employee';

                    $mainText = $activity->getExtraProperty('attributes.description') // For new complaints
                    ?? $activity->getExtraProperty('attributes.notes') // For updates
                    ?? '...';
                    @endphp

                    <tr>
                        <td class="p-3 border-l border-b text-center align-middle bg-slate-50/30" style="width: 100px;">
                            <div class="text-[12px] font-black text-slate-700 leading-tight">
                                {{ $activity->created_at->format('d') }}
                                <span class="{{ $config['color'] }} uppercase">{{ $activity->created_at->format('M') }}</span>
                            </div>
                            <div class="text-[9px] font-bold text-slate-400 mb-1">{{ $activity->created_at->format('Y') }}</div>
                            <div class="text-[8px] font-mono text-slate-500 bg-white border border-slate-200 rounded px-1">
                                {{ $activity->created_at->format('h:i A') }}
                            </div>
                        </td>

                        <td class="p-3 border-l border-b">
                            <div class="mb-2">
                                <div class="text-[11px] font-bold text-slate-800">
                                    @if($isNew)
                                    تقديم شكوى جديدة برقم:
                                    @elseif($isResolved)
                                    إغلاق وحل الشكوى رقم:
                                    @else
                                    رفض طلب الشكوى رقم:
                                    @endif
                                    <span class="text-blue-600 font-black">#{{ $refNum }}</span>
                                </div>
                                <div class="text-[9px] text-slate-400 uppercase font-bold tracking-tighter">
                                    @if($isNew)
                                    New Complaint Filed via Citizen Portal
                                    @elseif($isResolved)
                                    Case Resolution & Closure
                                    @else
                                    Case Rejection Notice
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-[9px] bg-white text-slate-600 px-1.5 py-0.5 rounded border border-slate-200 shadow-sm">
                                    <strong>{{ $roleLabelAr }} / {{ $roleLabelEn }}:</strong> {{ $personName }}
                                </span>
                            </div>

                            <div class="p-2 rounded bg-white border-r-4 {{ $config['border'] }} shadow-sm">
                                <div class="text-[9px] font-bold text-slate-500 mb-1 uppercase">
                                    {{ $isNew ? 'نص الشكوى / Complaint Content' : 'الملاحظات / Remarks' }}:
                                </div>
                                <div class="text-[10px] text-slate-700 leading-relaxed italic line-clamp-2">
                                    "{{ Str::limit($mainText, 150) }}"
                                </div>
                            </div>
                        </td>

                        <td class="p-3 border-b text-center align-middle" style="width: 90px;">
                            <div class="inline-block w-full py-2 rounded-lg {{ $config['bg'] }} {{ $config['color'] }} border {{ $config['border'] }} border-opacity-30 shadow-sm">
                                <div class="text-[11px] font-black">{{ $config['ar'] }}</div>
                                <div class="text-[8px] font-bold uppercase opacity-60">{{ $config['en'] }}</div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>