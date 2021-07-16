<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <title>Lesson 1</title>
</head>
<body>
<div class="container">
    <h4>Hệ thống chấm công cho nhân viên</h4>
    <p>Mô tả: chấm công trong 1 tháng, ngày nào đi thì mới chấm công</p>
    <table class="table">
        <thead>
        <tr>
            <th>Họ tên</th>
            <th>Mã Nhân viên</th>
            <th>Check in</th>
            <th>Hành động</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><input class="form-control" type="text" placeholder="Họ tên"></td>
            <td><input class="form-control" type="text" placeholder="Mã nhân viên" id="staff_id"></td>
            <td width="35%">
                <div class="input-group" id="nv">
                    <div>
                        <button style="margin:0 5px 5px 0" class="btn btn-outline-secondary _add_work">+</button>
                    </div>


<!--                    <span style="margin:0 5px 5px 0" class="badge bg-dark">-->
<!--                        02/02/2022 13:20-->
<!--                        <button type="button" class="btn btn-sm btn-danger">Xoá</button>-->
<!--                    </span>-->
                </div>
            </td>
            <td><button type="button" class="btn btn-primary">Xoá</button></td>
        </tr>

        </tbody>
    </table>
    <button type="button" class="btn btn-primary">Thêm dòng</button>
    <div class="mt-4 text-center">
        <button class="btn btn-success" type="button" id="submit">Lưu</button>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Thời gian</label>
                    <input class="form-control mb-4" type="date" id="checkin_date">
                    <input class="form-control" type="time" id="checkin_time">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary _set">Save changes</button>
            </div>
        </div>
    </div>
</div>
<script
    src="https://code.jquery.com/jquery-3.6.0.js"
    integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk="
    crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
<script>
    document.addEventListener("DOMContentLoaded",function (){
        $(this).on("click",'._add_work',function (){
            $("#exampleModal").modal('show');
        })

        $(this).on('click','._set',function (){
            $("#nv").append('<span style="margin:0 5px 5px 0" class="badge bg-dark">\n' +
                '                        02/02/2022 13:20\n' +
                '<button type="button" class="btn btn-sm btn-danger">Xoá</button>\n'+
                '                    </span>')
        })

        $('#submit').on('click', function () {
            $.ajax({
                type:'POST',
                url:'<?php echo site_url()?>'+'/ajax-time-keeping',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                data:({
                    'staff_id':$('#staff_id').val(),
                    'checkin_time':$('#checkin_time').val(),
                    'checkin_date':$('#checkin_date').val()
                }),
                success: function(data){
                    data = JSON.parse(data);
                    alert(data.content);
                }
            })
        });

    })

</script>
</body>
</html>