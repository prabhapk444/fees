<?php 
include 'db_connect.php';

$ef_id = $_GET['ef_id'];
$pid = $_GET['pid'];

$fees_query = $conn->query("SELECT ef.*, s.name AS sname, s.id_no, CONCAT(c.course,' - ',c.level) AS `class`, c.total_amount, c.duedate, c.dueamount 
                            FROM student_ef_list ef 
                            INNER JOIN student s ON s.id = ef.student_id 
                            INNER JOIN courses c ON c.id = ef.course_id  
                            WHERE ef.id = $ef_id");

$fees_row = $fees_query->fetch_assoc();

$ef_no = $fees_row['ef_no'];
$sname = $fees_row['sname'];
$class = $fees_row['class'];
$total_amount = $fees_row['total_amount'];
$duedate = $fees_row['duedate'];
$dueamount = $fees_row['dueamount'];

$payments_query = $conn->query("SELECT * FROM payments WHERE ef_id = $ef_id");
$pay_arr = array();
while($row = $payments_query->fetch_assoc()){
    $pay_arr[$row['id']] = $row;
}

$ptotal = 0;
foreach ($pay_arr as $row) {
    if (strtotime(date("Y-m-d")) > strtotime($duedate)) {
        $ptotal += $row['amount'] + $row['dueamount']; 
    } else {
        $ptotal += $row['amount']; 
    }
}

if (strtotime(date("Y-m-d")) > strtotime($duedate)) {
    $total_amount += $dueamount;
}

$balance = $total_amount - $ptotal;

?>

<style>
    .flex{
        display: inline-flex;
        width: 100%;
    }
    .w-50{
        width: 50%;
    }
    .text-center{
        text-align:center;
    }
    .text-right{
        text-align:right;
    }
    table.wborder{
        width: 100%;
        border-collapse: collapse;
    }
    table.wborder>tbody>tr, table.wborder>tbody>tr>td{
        border:1px solid;
    }
    p{
        margin:unset;
    }

</style>
<div class="container-fluid">
    <p class="text-center"><b><?php echo $pid == 0 ? "Payments" : 'Payment Receipt' ?></b></p>
    <hr>
    <div class="flex">
        <div class="w-50">
            <p>EF. No: <b><?php echo $ef_no ?></b></p>
            <p>Student: <b><?php echo ucwords($sname) ?></b></p>
            <p>Course/Level: <b><?php echo $class ?></b></p>
        </div>
        <?php if($pid > 0): ?>
        <div class="w-50">
            <p>Payment Date: <b><?php echo isset($pay_arr[$pid]) ? date("M d,Y",strtotime($pay_arr[$pid]['date_created'])): '' ?></b></p>
            <p>Paid Amount: <b><?php echo isset($pay_arr[$pid]) ? number_format($pay_arr[$pid]['amount'],2): '' ?></b></p>
            <p>Remarks: <b><?php echo isset($pay_arr[$pid]) ? $pay_arr[$pid]['remarks']: '' ?></b></p>
        </div>
        <?php endif; ?>
    </div>
    <hr>

    <p><b>Payment Details</b></p>
    <table width="100%" class="wborder">
        <tr>
            <td width="50%">Date</td>
            <td width="50%" class='text-right'>Amount</td>
        </tr>
        <?php 
        foreach ($pay_arr as $row) {
            if($row["id"] <= $pid || $pid == 0){
        ?>
        <tr>
            <td><b><?php echo date("Y-m-d",strtotime($row['date_created'])) ?></b></td>
            <td class='text-right'><b><?php echo number_format($row['amount']) ?></b></td>
        </tr>
        <?php
            }
        }
        ?>
        <tr>
            <th>Total</th>
            <th class='text-right'><b><?php echo number_format($ptotal) ?></b></th>
        </tr>
    </table>
    <table width="100%">
        <?php if (strtotime(date("Y-m-d")) > strtotime($duedate)): ?>
        <tr>
            <td>Total Payable Fee (if due date passed)</td>
            <td class='text-right'><b><?php echo number_format($total_amount) ?></b></td>
        </tr>
        <?php endif; ?>
        <tr>
            <td>Total Paid</td>
            <td class='text-right'><b><?php echo number_format($ptotal) ?></b></td>
        </tr>
        <tr>
            <td>Balance</td>
            <td class='text-right'><b><?php echo number_format($balance) ?></b></td>
        </tr>
    </table>
</div>
