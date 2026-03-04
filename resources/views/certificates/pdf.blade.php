<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Certificate of Completion</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        .container {
            border: 15px solid #1e3a8a; /* Deep blue border */
            padding: 40px;
            text-align: center;
            background-color: #ffffff;
            margin: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .inner-border {
            border: 2px solid #d1d5db;
            padding: 50px;
            position: relative;
        }
        .logo {
            font-size: 32px;
            font-weight: bold;
            color: #1e3a8a;
            margin-bottom: 20px;
        }
        .title {
            font-size: 48px;
            font-weight: bold;
            color: #1f2937;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }
        .subtitle {
            font-size: 20px;
            color: #6b7280;
            margin-bottom: 40px;
        }
        .student-name {
            font-size: 42px;
            font-weight: bold;
            color: #111827;
            border-bottom: 2px solid #d1d5db;
            display: inline-block;
            padding-bottom: 10px;
            margin-bottom: 40px;
            min-width: 500px;
        }
        .reason {
            font-size: 18px;
            color: #4b5563;
            margin-bottom: 20px;
        }
        .course-name {
            font-size: 28px;
            font-weight: bold;
            color: #1e3a8a;
            margin-bottom: 50px;
        }
        .footer {
            margin-top: 60px;
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }
        .footer-td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
        }
        .signature-line {
            border-top: 1px solid #1f2937;
            width: 200px;
            margin: 0 auto;
            padding-top: 10px;
            font-weight: bold;
            color: #1f2937;
        }
        .meta-info {
            position: absolute;
            bottom: 20px;
            left: 20px;
            font-size: 12px;
            color: #6b7280;
            text-align: left;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 120px;
            color: rgba(30, 58, 138, 0.05); /* very faint blue */
            z-index: 0;
            pointer-events: none;
        }
        /* Bring content above watermark */
        .content {
            position: relative;
            z-index: 10;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="inner-border">
            <div class="watermark">IQBRAVE</div>
            
            <div class="content">
                <div class="logo">IQBrave LMS</div>
                
                <div class="title">Certificate of Completion</div>
                
                <div class="subtitle">This is to proudly certify that</div>
                
                <div class="student-name">{{ $certificate->user->name }}</div>
                
                <div class="reason">has successfully completed all requirements, lessons, and quizzes for the course:</div>
                
                <div class="course-name">{{ $certificate->course->title }}</div>
                
                <div class="footer">
                    <table class="footer-table">
                        <tr>
                            <td class="footer-td">
                                <div style="margin-bottom: 10px; font-style: italic;">System Generated</div>
                                <div class="signature-line">IQBrave Administration</div>
                            </td>
                            <td class="footer-td">
                                <div style="margin-bottom: 10px;">{{ $certificate->issued_at->format('F j, Y') }}</div>
                                <div class="signature-line">Date of Issue</div>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="meta-info">
                    Certificate No: {{ $certificate->certificate_number }}<br>
                    Verify at: iqbrave.com/verify
                </div>
            </div>
        </div>
    </div>
</body>
</html>
