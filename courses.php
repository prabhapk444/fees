<?php include('db_connect.php');?>
<style>
	input[type=checkbox]
{
  
  -ms-transform: scale(1.3);
  -moz-transform: scale(1.3); 
  -webkit-transform: scale(1.3); 
  -o-transform: scale(1.3); 
  transform: scale(1.3);
  padding: 10px;
  cursor:pointer;
}
</style>
<div class="container-fluid">
	
	<div class="col-lg-12">
		<div class="row mb-4 mt-4">
			<div class="col-md-12">
				
			</div>
		</div>
		<div class="row">
  
			<!-- Table Panel -->
			<div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<b>List of Courses and Fees</b>
						<span class="float:right"><a class="btn btn-primary col-sm-6 col-md-2 float-right" href="javascript:void(0)" id="new_course">
					<i class="fa fa-plus"></i> New Entry
				</a></span>
					
					</div>
					<div class="card-body">
						<table class="table table-condensed table-bordered table-hover">
							<thead>
								<tr>
									<th class="text-center">#</th>
									<th class="">Course</th>
									<th class="">Year</th>
									<th class="">Total Fee</th>
									<th class="text-center">Action</th>
								</tr>
							</thead>
							<tbody>
								<?php 
								$i = 1;
								$course = $conn->query("SELECT * FROM courses  order by course asc ");
								while($row=$course->fetch_assoc()):
								?>
								<tr>
									<td class="text-center"><?php echo $i++ ?></td>
									<td>
										<?php echo $row['course'] . " - " . $row['level'] ?>
									</td>
									<td class="">
										 <p><?php echo $row['description'] ?></p>
									</td>
									<td class="text-right">
									 <?php echo number_format($row['total_amount'],2) ?>
									</td>
									<td class="text-center">
									<button class="btn btn-info edit_course" type="button" data-id="<?php echo $row['id'] ?>" ><i class="fa fa-edit"></i></button>
										<button class="btn btn-danger delete_course" type="button" data-id="<?php echo $row['id'] ?>"><i class="fa fa-trash-alt"></i></button>
										<button class="btn btn-success mail" type="button" data-id="<?php echo $row['id'] ?>">
    <i class="fa fa-envelope"></i>
</button>
									</td>
								</tr>
								<?php endwhile; ?>
							</tbody>
 
						</table>
					</div>
				</div>
			</div>
			<!-- Table Panel -->
		</div>
	</div>	

</div>
<style>
	
	td{
		vertical-align: middle !important;
	}
	td p{
		margin: unset
	}
	img{
		max-width:100px;
		max-height: :150px;
	}
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
	$(document).ready(function(){
		$('table').dataTable()
	})
	$('#new_course').click(function(){
		uni_modal("New Course and Fees Entry","manage_course.php",'large')
		
	})

	$('.edit_course').click(function(){
		uni_modal("Manage Course and Fees Entry","manage_course.php?id="+$(this).attr('data-id'),'large')
		
	})
	$('.delete_course').click(function(){
		_conf("Are you sure to delete this course?","delete_course",[$(this).attr('data-id')])
	})
	
	function delete_course($id){
		start_load()
		$.ajax({
			url:'ajax.php?action=delete_course',
			method:'POST',
			data:{id:$id},
			success:function(resp){
				if(resp==1){
					alert_toast("Data successfully deleted",'success')
					setTimeout(function(){
						location.reload()
					},1500)

				}
			}
		})
	}
	$(document).ready(function(){
    $('.mail').click(function(){
        var courseID = $(this).data('id');
        $.ajax({
            url: 'send_emails.php',
            method: 'POST',
            data: { course_id: courseID },
            success: function(response) {
            
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Emails sent successfully!'
                });
            },
            error: function(xhr, status, error) {
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error sending emails: ' + error
                });
            }
        });
    });
});

</script>
