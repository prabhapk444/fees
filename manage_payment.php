<?php include 'db_connect.php' ?>
<?php
if(isset($_GET['id'])){
	$qry = $conn->query("SELECT * FROM payments where id = {$_GET['id']} ");
	foreach($qry->fetch_array() as $k => $v){
		$$k = $v;
	}
}
?>
<div class="container-fluid">
	<form id="manage-payment">
		<div id="msg"></div>
		<input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
		<div class="form-group">
			<label for="" class="control-label">EF.NO./Student</label>
			<select name="ef_id" id="ef_id" class="custom-select input-sm select2">
				<option value=""></option>
				<?php
$fees = $conn->query("SELECT ef.*, s.name as sname, s.id_no FROM student_ef_list ef INNER JOIN student s ON s.id = ef.student_id ORDER BY s.name ASC");
$current_date = date('Y-m-d');

while ($row = $fees->fetch_assoc()) {
 
    $paid = $conn->query("SELECT SUM(amount) as paid FROM payments WHERE ef_id=".$row['id'].(isset($id) ? " AND id != $id" : ''));
    $paid = $paid->num_rows > 0 ? $paid->fetch_array()['paid'] : '';

   
    $balance = $row['total_fee'] - $paid;

    $sql = "SELECT duedate, dueamount FROM courses WHERE id = ".$row['course_id']; 
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $course_row = $result->fetch_assoc();
        $duedate = $course_row['duedate']; 
        $dueamount = $course_row['dueamount'];

       
        $days_overdue = max(0, strtotime($current_date) - strtotime($duedate)) / (60 * 60 * 24);

      
        $balance += $days_overdue * $dueamount;
    } else {
        $duedate = ''; 
        $dueamount = 0;
    }
?>
    <option value="<?php echo $row['id'] ?>" data-balance="<?php echo $balance ?>" data-duedate="<?php echo $duedate ?>" data-dueamount="<?php echo $dueamount ?>" <?php echo isset($ef_id) && $ef_id == $row['id'] ? 'selected' : '' ?>><?php echo  $row['ef_no'].' | '.ucwords($row['sname']) ?></option>
<?php
}
?>


				
				<?php
				$fees = $conn->query("SELECT ef.*, s.name AS sname, s.id_no, c.date_created
				FROM student_ef_list ef
				INNER JOIN student s ON s.id = ef.student_id
				INNER JOIN course c ON c.course_id = ef.course_id
				ORDER BY s.name ASC");

				?>
			</select>
		</div>
		 <div class="form-group">
            <label for="" class="control-label">course  Due Amount</label>
            <input type="text" class="form-control text-right" id="balance"  value="" required readonly>
        </div>
        <div class="form-group">
            <label for="" class="control-label">Amount</label>
            <input type="text" class="form-control text-right" name="amount"  value="<?php echo isset($amount) ? number_format($amount) :0 ?>" required>
        </div>
        <div class="form-group">
            <label for="" class="control-label">status</label>
            <textarea name="remarks" id="" cols="30" rows="3" class="form-control" required=""><?php echo isset($remarks) ? $remarks :'' ?></textarea>
        </div>
	</form>
</div>
<script>
	$('.select2').select2({
		placeholder:'Please select here',
		width:'100%'
	})
$('#ef_id').change(function () {
    var amount = $('#ef_id option[value="' + $(this).val() + '"]').attr('data-balance');
    
    var parsedAmount = parseFloat(amount);
  

    $('#balance').val(parsedAmount);
});


	$('#manage-payment').submit(function(e){
		e.preventDefault()
		start_load()
		$.ajax({
			url:'ajax.php?action=save_payment',
			method:'POST',
			data:$(this).serialize(),
			error:err=>{
				console.log(err)
				end_load()
			},
			success:function(resp){
				resp = JSON.parse(resp)
				if(resp.status == 1){
					alert_toast("Data successfully saved.",'success')
						setTimeout(function(){
							var nw = window.open('receipt.php?ef_id='+resp.ef_id+'&pid='+resp.pid,"_blank","width=900,height=600")
							setTimeout(function(){
								nw.print()
								setTimeout(function(){
									nw.close()
									location.reload()
								},500)
							},500)
						},500)
				}
			}
		})
	})
</script>