<?php
require './PHPMailer/src/PHPMailer.php'; 
require './PHPMailer/src/SMTP.php'; 
include('db_connect.php');
$data_json = $_GET['data'];
if(isset($data_json) && !empty($data_json)) {
    $data = json_decode($data_json);
    if($data !== null) {
        $student_id = $data->student_id;
        $course_id = $data->course_id;
        $sql = "SELECT duedate, course, total_amount, dueamount FROM courses WHERE id = $course_id";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $due_date = $row['duedate'];
            $course_name = $row['course'];
            $total_amount = $row['total_amount'];
            $due_amount = $row['dueamount'];
            $dayDifference = floor((strtotime(date('Y-m-d')) - strtotime($due_date)) / (60 * 60 * 24));
            $fine_amount = $dayDifference * $due_amount;
            $sql = "SELECT name, email FROM student WHERE id_no = $student_id";
            $result = $conn->query($sql);
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $student_name = $row['name'];
                $student_email = $row['email'];
                $total_due_amount = $total_amount + $due_amount;
                $mail = new PHPMailer\PHPMailer\PHPMailer();
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; 
                $mail->SMTPAuth = true;
                $mail->Username = 'karanprabha22668@gmail.com'; 
                $mail->Password = 'hrmq uoyw zory obcg'; 
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;
                $mail->setFrom('karanprabha22668@gmail.com', 'Admin'); 
                $mail->addAddress($student_email, $student_name);
                $mail->isHTML(true);
                $mail->Subject = 'Course Fee Reminder';
                $mail->Body = "
                <p>Dear $student_name,</p>
                <p>Your payment for the course \"$course_name\" is overdue. Please make the payment as soon as possible.</p>
                <p>Course Total Amount: ₹ $total_amount</p>
                <p>Due Amount: ₹ $due_amount</p>
                <p>Number of Days Overdue: $dayDifference</p>
                <p>Fine Amount for Late Payment: ₹ $fine_amount</p>
                <p>Total Amount Due: ₹ ".($total_amount +  $fine_amount)."</p>
                <p>Thank you.</p>
            ";
            
                $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
                if(!$mail->send()) {
                    echo 'Message could not be sent.';
                    echo 'Mailer Error: ' . $mail->ErrorInfo;
                } else {
                    echo 'Message has been sent';            
header("Location: index.php?page=fees");
exit;
                }
            } else {
                echo "No student found with the given ID.";
            }
        } else {
            echo "No course found with the given ID.";
        }
    } else {
        echo "Error decoding JSON data.";
    }
} else {
    echo "No data parameter found.";
}
?>
