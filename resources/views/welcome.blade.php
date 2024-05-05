<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title></title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../../dist/css/adminlte.min.css">
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
    <style>
        td {
            font-size: 20px;
            word-spacing: 10px;
            letter-spacing: 3px;
        }

        .dataTables_filter {
            visibility: hidden;
        }

        .highlight {
            background-color: yellow;
        }

    </style>

</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper" style="width: 100%;margin-left: 0px !important;">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <a href="/" style="color: #000">
                            BCR
                        </a>
                        |
                        <a href="/xoc-dia" style="color: #000">
                            XocDia
                        </a>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card card-warning">
                            <div class="card-header">
                                <h3 class="card-title">Search</h3>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <form role="form">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <!-- textarea -->
                                            <div class="form-group">
                                                <label>Nhập dãy số: <span id="total_list"></span> </label>
                                                <textarea class="form-control" rows="10" id="general_search"
                                                          placeholder="Enter ..."></textarea>
                                            </div>
                                        </div>
                                    </div>

                                </form>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- general form elements -->
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Quick Save</h3>
                            </div>
                            <!-- /.card-header -->
                            <!-- form start -->
                            <form role="form" id="formNumbers" action="/store">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Nhập dãy số</label>
                                        <textarea class="form-control" rows="10" id="exampleInputEmail1" name="numbers"
                                                  placeholder="Nhập dãy số....."></textarea>
                                    </div>
                                </div>
                                <!-- /.card-body -->

                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </form>
                        </div>
                        <!-- /.card -->
                    </div>
                    <div class="col-md-8">
                        <!-- general form elements -->
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Kết quả tìm kiếm: <span id="total"
                                                                               class="font-weight-bold">{{ $totalRecords }}</span>
                                </h3>
                                <button class="btn btn-dark" style="float: right">
                                    <a href="/download" style="color: white">
                                        File 👇
                                    </a>
                                </button>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <table id="example2" class="table table-responsive table-bordered table-hover"
                                       width="100%">
                                    <thead>
                                    <tr>
                                        <th width="10%">STT</th>
                                        <th width="100%">Dãy số</th>
                                        <th width="10%">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <th>STT</th>
                                        <th>Dãy số</th>
                                        <th>Action</th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->

                    </div>
                </div>
                <!-- /.row -->
            </div><!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- bs-custom-file-input -->
<script src="../../plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
<!-- AdminLTE App -->
<script src="../../dist/js/adminlte.min.js"></script>

<script src="../../plugins/datatables/jquery.dataTables.js"></script>
<script src="../../plugins/datatables-bs4/js/dataTables.bootstrap4.js"></script>
<!-- AdminLTE for demo purposes -->
<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    const table = $('#example2').DataTable({
        headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
        processing: true,
        serverSide: true,
        ajax: {
            url: "/data",
            dataType: "json",
            type: "POST",
            data: function (d) {
                d.start = d.start;
                d.length = d.length;
                d.search = $('#general_search').val();
            },
            dataSrc: 'data'
        },
        columns: [
            {data: "id"},
            {data: "numbers"},
        ],
        columnDefs: [
            {
                targets: 0, render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            {
                targets: 1, render: function (value, type, object, meta) {
                    $('#total').text(meta.settings._iRecordsDisplay);

                    let searchTerm = $('#general_search').val();

                    if (searchTerm) {
                        return value.replace(new RegExp(searchTerm, 'gi'), '<span class="highlight">$&</span>');
                    }

                    return value;
                }
            },
            {
                targets: 2, render: function (data, type, row, meta) {
                    return '<a href="#" onclick="deleteRecord(' + row.id + ')"><i class="fa fa-trash"></i></a>';
                }
            },
        ],
    });

    $('#general_search').keyup(function () {
        let myFunc = num => Number(num);
        let list = Array.from(String(this.value), myFunc);
        let total = 0;
        for (var i in list) total += Number(list[i]);
        $('#total_list').text(total)
        table.search(this.value).draw();
    });

    $('#formNumbers').submit(function (event) {
        event.preventDefault();

        var formData = $(this).serialize();

        $.ajax({
            url: '/store',
            type: 'POST',
            data: formData,
            success: function (response) {
                if ($('#general_search').val()) {
                    table.search($('#general_search').val()).draw();
                } else {
                    table.draw();
                }

                $('#exampleInputEmail1').val('')
            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    });


    function deleteRecord(id) {
        $.ajax({
            url: '/delete/' + id,
            type: 'DELETE',
            success: function (response) {
                table.draw();
            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    }

</script>
</body>
</html>
