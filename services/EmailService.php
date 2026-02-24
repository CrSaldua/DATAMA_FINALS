<?php
// Load Composer's Autoloader
require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {

    // --- CONFIGURATION ---
    private $smtp_host = 'smtp.gmail.com';
    private $smtp_user = 'cristian058108@gmail.com'; // <--- REPLACE THIS
    private $smtp_pass = 'rsqh dopo xxix blro';   // <--- REPLACE THIS
    private $smtp_port = 587;

    // UPDATED: Using the direct image link instead of the article link

    private $lebron_img = "https://ftw.usatoday.com/gcdn/authoring/images/smg/2025/02/22/SFTW/79536671007-90-2317758.png?width=1320&height=744&fit=crop&format=pjpg&auto=webp";

    private function saveEmailLocally($to, $subject, $body) {
        $folder = __DIR__ . '/../sent_emails';
        if (!is_dir($folder)) mkdir($folder, 0777, true);
        $filename = $folder . '/Email_' . date('Y-m-d_H-i-s') . '_' . str_replace(['@', '.'], '_', $to) . '.html';
        file_put_contents($filename, $body);
    }

    private function sendRealEmail($to, $subject, $body) {
        $mail = new PHPMailer(true);
        try {
            // FIX 1: Force UTF-8 Encoding
            $mail->CharSet = 'UTF-8'; 
            $mail->Encoding = 'base64';

            $mail->isSMTP();
            $mail->Host       = $this->smtp_host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->smtp_user;
            $mail->Password   = $this->smtp_pass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $this->smtp_port;

            $mail->setFrom($this->smtp_user, 'City Library System');
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            $this->saveEmailLocally($to, "$subject (FAILED)", $body);
            return false;
        }
    }

    public function sendReturnReceipt($email, $memberName, $bookTitle, $fine, $totalPaid) {
        $subject = "Library Receipt: Book Returned";
        
        // FIX 2: Used &#8369; instead of the raw symbol
        $message = "
        <html>
        <body style='font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px;'>
            <div style='background: white; padding: 20px; border-radius: 8px; max-width: 500px; margin: auto; border-top: 5px solid #10b981;'>
                <h2 style='color: #10b981; margin-top:0;'>Book Returned Successfully</h2>
                <p>Hi <strong>$memberName</strong>,</p>
                <p>This email confirms that you have returned the following item:</p>
                
                <div style='background: #f0fdf4; padding: 15px; border-radius: 6px; margin: 15px 0; border: 1px solid #bbf7d0;'>
                    <h3 style='margin:0 0 5px 0;'>$bookTitle</h3>
                    <span style='font-size: 13px; color: #166534;'>Returned on " . date('M d, Y') . "</span>
                </div>

                <table style='width: 100%; margin-top: 15px;'>
                    <tr>
                        <td style='color: #6b7280;'>Fine Amount:</td>
                        <td style='text-align: right; font-weight: bold;'>&#8369;" . number_format($fine, 2) . "</td>
                    </tr>
                    <tr>
                        <td style='color: #6b7280;'>Total Paid:</td>
                        <td style='text-align: right; font-weight: bold; color: #10b981;'>&#8369;" . number_format($totalPaid, 2) . "</td>
                    </tr>
                </table>
                
                <div style='margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px; text-align: center;'>
                    <p style='font-size: 12px; font-weight: bold; color: #555;'>KEEP READING, KEEP STRIVING!</p>
                    <img src='{$this->lebron_img}' style='width: 100px; border-radius: 50%; border: 3px solid #f59e0b;'>
                    <p style='font-size: 11px; color: #888;'>- LeBron James, Library Ambassador</p>
                </div>
            </div>
        </body>
        </html>
        ";
        return $this->sendRealEmail($email, $subject, $message);
    }

    public function sendConsolidatedOverdueNotice($email, $memberName, $books, $grandTotal) {
        $count = count($books);
        $subject = "URGENT: Outstanding Fines for $count Item(s)";

        $bookListRows = "";
        foreach ($books as $b) {
            // FIX 2: Used &#8369; here as well
            $bookListRows .= "
            <tr>
                <td style='padding: 10px; border-bottom: 1px solid #e5e7eb;'><strong>{$b['title']}</strong></td>
                <td style='padding: 10px; border-bottom: 1px solid #e5e7eb; color: #ef4444;'>{$b['due_date']}</td>
                <td style='padding: 10px; border-bottom: 1px solid #e5e7eb; text-align: center;'>{$b['days_late']}</td>
                <td style='padding: 10px; border-bottom: 1px solid #e5e7eb; text-align: right; font-weight: bold;'>&#8369;{$b['fine']}</td>
            </tr>";
        }

        $message = "
        <html>
        <body style='font-family: Arial, sans-serif; background: #fef2f2; padding: 20px;'>
            <div style='background: white; padding: 30px; border-radius: 8px; max-width: 600px; margin: auto; border-top: 5px solid #ef4444; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);'>
                
                <h2 style='color: #ef4444; margin-top: 0;'>Action Required: Overdue Items</h2>
                <p>Dear <strong>$memberName</strong>,</p>
                <p>Our records indicate that you have <strong>$count</strong> overdue item(s).</p>
                
                <table style='width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 14px;'>
                    <tr style='background: #fee2e2; color: #991b1b;'>
                        <th style='padding: 10px; text-align: left;'>Book Title</th>
                        <th style='padding: 10px; text-align: left;'>Due Date</th>
                        <th style='padding: 10px; text-align: center;'>Days Late</th>
                        <th style='padding: 10px; text-align: right;'>Fine</th>
                    </tr>
                    $bookListRows
                    <tr style='background: #f8fafc;'>
                        <td colspan='3' style='padding: 12px; text-align: right; font-weight: bold; color: #374151;'>TOTAL AMOUNT DUE:</td>
                        <td style='padding: 12px; text-align: right; font-weight: bold; color: #ef4444; font-size: 18px;'>&#8369;$grandTotal</td>
                    </tr>
                </table>
                
                <div style='background: #1f2937; color: white; padding: 20px; border-radius: 8px; margin-top: 25px; display: flex; align-items: center; gap: 15px;'>
                    <img src='{$this->lebron_img}' style='width: 80px; height: 80px; object-fit: cover; border-radius: 50%; border: 3px solid #f59e0b;'>
                    <div>
                        <p style='margin: 0; font-style: italic; font-size: 14px;'>\"The only way to get better is to be accountable. Return your books so others can learn too.\"</p>
                        <p style='margin: 5px 0 0 0; font-weight: bold; color: #f59e0b; font-size: 12px;'>- LeBron James, Library Ambassador</p>
                    </div>
                </div>

                <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
                <p style='font-size: 12px; color: #9ca3af; text-align: center;'>Please visit the library desk to settle this amount.</p>
            </div>
        </body>
        </html>
        ";
        return $this->sendRealEmail($email, $subject, $message);
    }
}
?>