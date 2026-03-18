<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Certificate of Completion</title>
    <style>
        @page {
            margin: 0px;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0px;
            padding: 0px;
            background-color: #ffffff;
            color: #333333;
        }
        .cert-outer-border {
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            bottom: 20px;
            border: 10px solid #b8860b; /* Dark Goldenrod */
        }
        .cert-inner-border {
            position: absolute;
            top: 10px;
            left: 10px;
            right: 10px;
            bottom: 50px;
            border: 3px solid #daa520; /* Goldenrod */
            text-align: center;
        }
        .watermark {
            position: absolute;
            top: 35%;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 80px;
            color: rgba(218, 165, 32, 0.08);
            z-index: -1;
            font-weight: bold;
        }
        .content {
            margin-top: 80px;
        }
        .logo {
            font-size: 36px;
            font-weight: bold;
            color: #b8860b;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .title {
            font-size: 48px;
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 4px;
            margin-bottom: 30px;
        }
        .subtitle {
            font-size: 20px;
            color: #4b5563;
            margin-bottom: 40px;
        }
        .student-name {
            font-size: 56px;
            font-weight: bold;
            color: #b8860b; /* Gold */
            border-bottom: 2px solid #d1d5db;
            display: inline-block;
            padding-bottom: 10px;
            margin-bottom: 40px;
            min-width: 600px;
        }
        .reason {
            font-size: 20px;
            color: #4b5563;
            margin-bottom: 30px;
        }
        .course-name {
            font-size: 34px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 0;
        }
        .footer-container {
            position: absolute;
            bottom: 80px;
            left: 50px;
            right: 50px;
            width: auto;
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .footer-td {
            width: 33.33%;
            vertical-align: bottom;
        }
        .signature-line {
            border-bottom: 2px solid #1f2937;
            width: 200px;
            margin-bottom: 10px;
        }
        .signature-text {
            font-size: 16px;
            color: #374151;
            font-weight: bold;
        }
        .date-text {
            font-size: 18px;
            color: #111827;
            margin-bottom: 15px;
        }
        .qr-code {
            text-align: right;
            padding-right: 10px;
        }
        .cert-number {
            position: absolute;
            bottom: -22px;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 11px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="cert-outer-border">
        <div class="cert-inner-border">
            <div class="watermark">{{ strtoupper(config('app.name')) }}</div>
            
            <div class="content">
                <div class="logo">{{ config('app.name') }}</div>
                
                <div class="title">Certificate of Completion</div>
                
                <div class="subtitle">This certifies that</div>
                
                <div class="student-name">{{ $certificate->user->name }}</div>
                
                <div class="reason">has successfully completed</div>
                
                <div class="course-name">{{ $certificate->course->title }}</div>
            </div>
            
            <div class="footer-container">
                <table class="footer-table">
                    <tr>
                        <td class="footer-td" style="text-align: left;">
                            <div class="date-text">{{ $certificate->issued_at->format('F j, Y') }}</div>
                            <div class="signature-line"></div>
                            <div class="signature-text">Date</div>
                        </td>
                        <td class="footer-td" style="text-align: center;">
                            <div class="date-text" style="font-style: italic; color:#6b7280; font-size:16px;">System Verified</div>
                            <div class="signature-line" style="margin: 0 auto 10px auto;"></div>
                            <div class="signature-text">{{ config('app.name') }} Administrator</div>
                        </td>
                        <td class="footer-td qr-code">
                            @if(isset($qr))
                                <img src="data:image/svg+xml;base64,{{ $qr }}" style="width:120px; height:120px;">
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="cert-number">
                Certificate No: {{ $certificate->certificate_number }}
            </div>
        </div>
    </div>
</body>
</html>
