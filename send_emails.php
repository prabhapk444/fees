<?php

require './PHPMailer/src/PHPMailer.php'; 
require './PHPMailer/src/SMTP.php'; 

include('db_connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['course_id'])) {
 
    $courseID = mysqli_real_escape_string($conn, $_POST['course_id']);

    $sql = "SELECT course, total_amount, duedate,dueamount FROM courses WHERE id = $courseID";
    $result = $conn->query($sql);

    if ($result) {
        $row = $result->fetch_assoc();
        $courseName = $row["course"];
        $amount = (int)$row["total_amount"];
        $dueDate = $row["duedate"];
        $dueAmount=$row["dueamount"];

        $currentDate = date('Y-m-d');
        $daysPastDue = max(0, strtotime($currentDate) - strtotime($dueDate)) / (60 * 60 * 24);

        $studentsQuery = "SELECT s.email, s.name FROM student s JOIN courses c ON s.course_id = c.id WHERE c.id = $courseID";
        $studentsResult = $conn->query($studentsQuery);

        if ($studentsResult) {
            while ($student = $studentsResult->fetch_assoc()) {
                $studentEmail = $student['email'];
                $studentName = $student['name']; 

                $mail = new PHPMailer\PHPMailer\PHPMailer();
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'karanprabha22668@gmail.com';
                $mail->Password = 'hrmq uoyw zory obcg';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('karanprabha22668@gmail.com', 'Admin');
                $mail->addAddress($studentEmail);
                $mail->Subject = 'Payment Reminder for ' . $courseName;

                
                $fineAmountPerDay = $dueAmount;
                $fineAmount = $daysPastDue > 0 ? $daysPastDue * $fineAmountPerDay : 0;

              
                $totalAmountDue = $amount + $fineAmount;

                
                $amountWithSymbol = '₹' . number_format($amount, 0);
                $fineAmountWithSymbol = '₹' . number_format($fineAmount, 0);
                $totalAmountDueWithSymbol = '₹' . number_format($totalAmountDue, 0);

                // Include the student's name in the email body
                $mail->Body = 'Dear ' . $studentName . ', Please be reminded to pay the amount of ' . $amountWithSymbol . ' for the course: ' . $courseName . '. The payment is due by ' . $dueDate .  '. The due amount is: ' . $dueAmount . '.(Due amount is increased until you pay your full fees)';

               
                if ($daysPastDue > 0) {
                    $mail->Body .= ' A fine of ' . $fineAmountWithSymbol . ' has been added for being ' . $daysPastDue . ' days past due. ';
                    $mail->Body .= 'The total amount due is ' . $totalAmountDueWithSymbol . '.';
                }

                if (!$mail->send()) {
                    echo 'Error sending email to ' . $studentEmail . ': ' . $mail->ErrorInfo;
                } else {
                    echo 'Email sent successfully to ' . $studentEmail . '<br>';
                }
            }
        } else {
            echo 'No students found.';
        }
    } else {
        echo "Error fetching course information: " . $conn->error;
    }
} else {
    echo "Invalid request.";
}

$conn->close();
?>
