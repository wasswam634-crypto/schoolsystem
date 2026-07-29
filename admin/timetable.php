
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../database/connection.php';
include '../includes/auth.php';
include '../includes/functions.php';

require_role('admin');

$message = "";
$error = "";


/*
|--------------------------------------------------------------------------
| LOAD ACTIVE ACADEMIC PERIOD
|--------------------------------------------------------------------------
*/

$period_query = mysqli_query(
    $conn,
    "SELECT *
     FROM academic_periods
     WHERE status='Active'
     LIMIT 1"
);

$active_period = mysqli_fetch_assoc($period_query);

if(!$active_period){

    die("
    <div style='padding:40px;font-family:Arial'>
        <h2>No Active Academic Period</h2>

        Please create and activate an academic period first.
    </div>
    ");

}

$period_id      = $active_period['period_id'];
$academic_year  = $active_period['academic_year'];
$period_name    = $active_period['period_name'];


/*
|--------------------------------------------------------------------------
| LOAD SUBJECTS
|--------------------------------------------------------------------------
*/

$subjects=[];

$result=mysqli_query(
$conn,
"SELECT subject_id,subject_name
 FROM subjects
 ORDER BY subject_name"
);

while($row=mysqli_fetch_assoc($result)){

    $subjects[]=$row;

}



/*
|--------------------------------------------------------------------------
| LOAD TEACHERS
|--------------------------------------------------------------------------
*/

$teachers=[];

$result=mysqli_query(
$conn,
"SELECT teacher_id,full_name
 FROM teachers
 ORDER BY full_name"
);

while($row=mysqli_fetch_assoc($result)){

    $teachers[]=$row;

}



/*
|--------------------------------------------------------------------------
| SAVE TIMETABLE
|--------------------------------------------------------------------------
*/

if(isset($_POST['save'])){


$class=trim($_POST['class']);

$days=$_POST['day'];

$starts=$_POST['start_time'];

$ends=$_POST['end_time'];

$periods=$_POST['period_name'];

$types=$_POST['lesson_type'];

$subject_ids=$_POST['subject_id'];

$teacher_ids=$_POST['teacher_id'];

$rooms=$_POST['room'];



$success=true;



for($i=0;$i<count($days);$i++){


if(empty($starts[$i])){

continue;

}



/*
---------------------------------------
CHECK CLASS CONFLICT
---------------------------------------
*/

$stmt=mysqli_prepare(

$conn,

"SELECT timetable_id

FROM timetables

WHERE

period_id=?

AND class=?

AND day=?

AND start_time < ?

AND end_time > ?"

);

mysqli_stmt_bind_param(

$stmt,

"issss",

$period_id,

$class,

$days[$i],

$ends[$i],

$starts[$i]

);

mysqli_stmt_execute($stmt);

$result=mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)>0){

$error="Class already has another lesson during this time.";

$success=false;

break;

}



/*
---------------------------------------
CHECK TEACHER
---------------------------------------
*/

$stmt=mysqli_prepare(

$conn,

"SELECT timetable_id

FROM timetables

WHERE

period_id=?

AND teacher_id=?

AND day=?

AND start_time < ?

AND end_time > ?"

);

mysqli_stmt_bind_param(

$stmt,

"iisss",

$period_id,

$teacher_ids[$i],

$days[$i],

$ends[$i],

$starts[$i]

);

mysqli_stmt_execute($stmt);

$result=mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)>0){

$error="Teacher has another lesson at this time.";

$success=false;

break;

}



/*
---------------------------------------
CHECK ROOM
---------------------------------------
*/

$stmt=mysqli_prepare(

$conn,

"SELECT timetable_id

FROM timetables

WHERE

period_id=?

AND room=?

AND day=?

AND start_time < ?

AND end_time > ?"

);

mysqli_stmt_bind_param(

$stmt,

"issss",

$period_id,

$rooms[$i],

$days[$i],

$ends[$i],

$starts[$i]

);

mysqli_stmt_execute($stmt);

$result=mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)>0){

$error="Room already occupied.";

$success=false;

break;

}


}



/*
|--------------------------------------------------------------------------
| SAVE IF NO CONFLICT
|--------------------------------------------------------------------------
*/

if($success){

for($i=0;$i<count($days);$i++){

if(empty($starts[$i])){

continue;

}

$stmt=mysqli_prepare(

$conn,

"INSERT INTO timetables(

period_id,
academic_year,
period_name,
class,
day,
start_time,
end_time,
lesson_type,
subject_id,
teacher_id,
room

)

VALUES(

?,?,?,?,?,?,?,?,?,?,?

)"

);

mysqli_stmt_bind_param(

$stmt,

"isssssssiis",

$period_id,
$academic_year,
$periods[$i],
$class,
$days[$i],
$starts[$i],
$ends[$i],
$types[$i],
$subject_ids[$i],
$teacher_ids[$i],
$rooms[$i]

);

mysqli_stmt_execute($stmt);

}

$message="Timetable saved successfully.";

}

}

?>
<!DOCTYPE html>

<html>

<head>

<title>Timetable Management</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<style>

body{
background:#f4f6f9;
}

.card{
border:none;
border-radius:15px;
box-shadow:0 4px 15px rgba(0,0,0,.08);
}

.card-header{
font-weight:bold;
font-size:18px;
}

.lesson-row{
background:#fff;
padding:20px;
margin-bottom:15px;
border-radius:10px;
border-left:5px solid #198754;
box-shadow:0 2px 8px rgba(0,0,0,.08);
}

.preview-cell{
min-height:80px;
}

</style>

</head>

<body>

<div class="container-fluid p-4">

<h2 class="mb-4">
📅 Timetable Builder
</h2>


<?php if($message): ?>

<div class="alert alert-success">

<?=e($message);?>

</div>

<?php endif; ?>


<?php if($error): ?>

<div class="alert alert-danger">

<?=e($error);?>

</div>

<?php endif; ?>



<div class="alert alert-info">

<b>Current Academic Period</b>

<br>

Academic Year:
<b><?=e($academic_year);?></b>

<br>

Period:
<b><?=e($period_name);?></b>

</div>



<form method="POST">



<div class="card mb-4">

<div class="card-header bg-primary text-white">

General Information

</div>

<div class="card-body">

<div class="row">

<div class="col-md-6">

<label class="fw-bold">

Class

</label>

<input
type="text"
name="class"
class="form-control"
placeholder="Example: S1 East"
required>

</div>

</div>

</div>

</div>




<div class="card">

<div class="card-header bg-success text-white d-flex justify-content-between">

<span>

Lessons

</span>

<button
type="button"
class="btn btn-light btn-sm"
onclick="addLesson()">

+ Add Lesson

</button>

</div>



<div class="card-body" id="lessonContainer">

<div class="lesson-row">

<div class="row g-3">



<div class="col-md-2">

<label>Period</label>

<select
name="period_name[]"
class="form-control">

<option>Period 1</option>
<option>Period 2</option>
<option>Period 3</option>
<option>Period 4</option>
<option>Period 5</option>
<option>Break</option>

</select>

</div>




<div class="col-md-2">

<label>Day</label>

<select
name="day[]"
class="form-control">

<option>Monday</option>
<option>Tuesday</option>
<option>Wednesday</option>
<option>Thursday</option>
<option>Friday</option>

</select>

</div>




<div class="col-md-2">

<label>Start</label>

<input
type="time"
name="start_time[]"
class="form-control">

</div>




<div class="col-md-2">

<label>End</label>

<input
type="time"
name="end_time[]"
class="form-control">

</div>




<div class="col-md-2">

<label>Lesson Type</label>

<select
name="lesson_type[]"
class="form-control">

<option>Normal Lesson</option>
<option>Double Lesson</option>
<option>Break</option>
<option>Assembly</option>
<option>Sports</option>

</select>

</div>




<div class="col-md-2">

<label>Room</label>

<input
type="text"
name="room[]"
class="form-control">

</div>




<div class="col-md-3">

<label>Subject</label>

<select
name="subject_id[]"
class="form-control">

<?php foreach($subjects as $subject): ?>

<option value="<?=$subject['subject_id'];?>">

<?=e($subject['subject_name']);?>

</option>

<?php endforeach; ?>

</select>

</div>




<div class="col-md-3">

<label>Teacher</label>

<select
name="teacher_id[]"
class="form-control">

<?php foreach($teachers as $teacher): ?>

<option value="<?=$teacher['teacher_id'];?>">

<?=e($teacher['full_name']);?>

</option>

<?php endforeach; ?>

</select>

</div>




<div class="col-md-2 d-flex align-items-end">

<button
type="button"
class="btn btn-danger"
onclick="removeLesson(this)">

Remove

</button>

</div>

</div>

</div>

</div>

</div>



<br>

<button
type="button"
class="btn btn-success btn-lg"
onclick="generatePreview()">

👁 Preview Timetable

</button>


<button
type="submit"
name="save"
class="btn btn-primary btn-lg">

💾 Save Timetable

</button>

</form>



<div
class="card mt-4"
id="previewSection"
style="display:none;">

<div class="card-header bg-dark text-white">

Timetable Preview

</div>

<div class="card-body">

<table class="table table-bordered text-center">

<thead class="table-dark">

<tr>

<th>Time</th>

<th>Monday</th>

<th>Tuesday</th>

<th>Wednesday</th>

<th>Thursday</th>

<th>Friday</th>

</tr>

</thead>

<tbody id="previewTable">

</tbody>

</table>

</div>

</div>


<script>

function addLesson(){

    let container = document.getElementById("lessonContainer");

    let lesson = document.querySelector(".lesson-row").cloneNode(true);

    lesson.querySelectorAll("input").forEach(input=>{
        input.value="";
    });

    lesson.querySelectorAll("select").forEach(select=>{
        select.selectedIndex=0;
    });

    container.appendChild(lesson);

}



function removeLesson(button){

    let rows=document.querySelectorAll(".lesson-row");

    if(rows.length>1){

        button.closest(".lesson-row").remove();

    }

}




function generatePreview(){

    let rows=document.querySelectorAll(".lesson-row");

    let timetable={};

    rows.forEach(function(row){

        let period=row.querySelector("select[name='period_name[]']").value;

        let day=row.querySelector("select[name='day[]']").value;

        let start=row.querySelector("input[name='start_time[]']").value;

        let end=row.querySelector("input[name='end_time[]']").value;

        let subject=row.querySelector("select[name='subject_id[]'] option:checked").text;

        let teacher=row.querySelector("select[name='teacher_id[]'] option:checked").text;

        let room=row.querySelector("input[name='room[]']").value;

        let lesson=row.querySelector("select[name='lesson_type[]']").value;


        if(start==""){

            return;

        }

        let key=start+" - "+end;


        if(!timetable[key]){

            timetable[key]={};

        }


        let colour="#198754";


        switch(lesson){

            case "Break":
                colour="#ffc107";
                break;

            case "Assembly":
                colour="#0d6efd";
                break;

            case "Sports":
                colour="#dc3545";
                break;

            case "Double Lesson":
                colour="#6f42c1";
                break;

        }


        timetable[key][day]=`

        <div style="
        background:${colour};
        color:white;
        border-radius:8px;
        padding:8px;
        font-size:13px;
        ">

        <strong>${period}</strong><br>

        ${subject}<br>

        ${teacher}<br>

        Room ${room}

        </div>

        `;

    });




    let tbody=document.getElementById("previewTable");

    tbody.innerHTML="";



    let weekdays=[

        "Monday",
        "Tuesday",
        "Wednesday",
        "Thursday",
        "Friday"

    ];



    Object.keys(timetable)

    .sort()

    .forEach(function(time){


        let tr=document.createElement("tr");

        let html="<td><b>"+time+"</b></td>";


        weekdays.forEach(function(day){

            if(timetable[time][day]){

                html+="<td>"+timetable[time][day]+"</td>";

            }

            else{

                html+="<td>-</td>";

            }

        });


        tr.innerHTML=html;

        tbody.appendChild(tr);

    });



    document.getElementById("previewSection").style.display="block";

}

</script>


</body>
</html>