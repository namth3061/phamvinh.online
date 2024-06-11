<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Table</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        table {
            width: 100%;
            margin-bottom: 20px;
        }
        th, td {
            text-align: center;
            vertical-align: middle;
            height: 50px;
        }
        .fixed-header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: #fff;
            z-index: 1000;
            padding: 10px;
            border-bottom: 1px solid #ccc;
        }

        .active {
            background: #ffff002e;
        }
    </style>
</head>
<body>
<div class="fixed-header">
    <div class="container">
        <div class="row">
            <div class="col">
                <button id="redButton" class="btn btn-danger">Red</button>
                <button id="greenButton" class="btn btn-success">Green</button>
                <button id="blueButton" class="btn btn-primary">Blue</button>
                <button id="addTableButton" class="btn btn-warning">Add Table</button>
                <button id="saveTablesButton" class="btn btn-info">Save Tables</button>
            </div>
            <div class="col">
                <input type="text" id="searchInput" class="form-control" placeholder="Red Blue Green">
                <button id="findTableButton" class="btn btn-secondary mt-2">Find Matching Table</button>
            </div>
        </div>
    </div>
</div>

<div class="container mt-5">
    <div id="tablesContainer">
       @if ($tables)
            {!!$tables!!}
       @else 
            <!-- default table -->
            <div class="table-responsive">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <td>Row 1 Col 1</td>
                            <td>Row 1 Col 2</td>
                            <td>Row 1 Col 3</td>
                            <td>Row 1 Col 4</td>
                            <td>Row 1 Col 5</td>
                            <td>Row 1 Col 6</td>
                            <td>Row 1 Col 7</td>
                            <td>Row 1 Col 8</td>
                        </tr>
                        <tr>
                            <td>Row 2 Col 1</td>
                            <td>Row 2 Col 2</td>
                            <td>Row 2 Col 3</td>
                            <td>Row 2 Col 4</td>
                            <td>Row 2 Col 5</td>
                            <td>Row 2 Col 6</td>
                            <td>Row 2 Col 7</td>
                            <td>Row 2 Col 8</td>
                        </tr>
                        <tr>
                            <td>Row 3 Col 1</td>
                            <td>Row 3 Col 2</td>
                            <td>Row 3 Col 3</td>
                            <td>Row 3 Col 4</td>
                            <td>Row 3 Col 5</td>
                            <td>Row 3 Col 6</td>
                            <td>Row 3 Col 7</td>
                            <td>Row 3 Col 8</td>
                        </tr>
                        <tr>
                            <td>Row 4 Col 1</td>
                            <td>Row 4 Col 2</td>
                            <td>Row 4 Col 3</td>
                            <td>Row 4 Col 4</td>
                            <td>Row 4 Col 5</td>
                            <td>Row 4 Col 6</td>
                            <td>Row 4 Col 7</td>
                            <td>Row 4 Col 8</td>
                        </tr>
                        <tr>
                            <td>Row 5 Col 1</td>
                            <td>Row 5 Col 2</td>
                            <td>Row 5 Col 3</td>
                            <td>Row 5 Col 4</td>
                            <td>Row 5 Col 5</td>
                            <td>Row 5 Col 6</td>
                            <td>Row 5 Col 7</td>
                            <td>Row 5 Col 8</td>
                        </tr>
                        <tr>
                            <td>Row 6 Col 1</td>
                            <td>Row 6 Col 2</td>
                            <td>Row 6 Col 3</td>
                            <td>Row 6 Col 4</td>
                            <td>Row 6 Col 5</td>
                            <td>Row 6 Col 6</td>
                            <td>Row 6 Col 7</td>
                            <td>Row 6 Col 8</td>
                        </tr>
                    </tbody>
                </table>
            </div>
       @endif
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        var selectedColor = '';

        $('#redButton').click(function() {
            selectedColor = 'red';
        });

        $('#greenButton').click(function() {
            selectedColor = 'green';
        });

        $('#blueButton').click(function() {
            selectedColor = 'blue';
        });

        $('#tablesContainer').on('click', 'table td', function() {
            if (selectedColor) {
                $(this).css('background-color', selectedColor);
            }
        });

        $('#findTableButton').click(function() {
            var input = $('#searchInput').val().trim();
            var counts = input.split(' ').map(Number);
            if (counts.length !== 3) {
                alert('Please enter three numbers separated by spaces.');
                return;
            }
            var redCount = counts[0];
            var blueCount = counts[1];
            var greenCount = counts[2];
            var found = false;
            $('.active').removeClass('active');
            $('#tablesContainer .table-responsive').each(function(index, tableDiv) {
                var table = $(tableDiv).find('table');
                var reds = table.find('td[style*="background-color: red"]').length;
                var blues = table.find('td[style*="background-color: blue"]').length;
                var greens = table.find('td[style*="background-color: green"]').length;

                if (reds === redCount && blues === blueCount && greens === greenCount) {
                    alert('Found matching table at index: ' + index);
                    $(this).addClass('active');
                    found = true;
                    return false;
                }
            });

            if (!found) {
                alert('No matching table found.');
            }
        });

        $('#addTableButton').click(function() {
            var newTable = `
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <td>Row 1 Col 1</td>
                                <td>Row 1 Col 2</td>
                                <td>Row 1 Col 3</td>
                                <td>Row 1 Col 4</td>
                                <td>Row 1 Col 5</td>
                                <td>Row 1 Col 6</td>
                                <td>Row 1 Col 7</td>
                                <td>Row 1 Col 8</td>
                            </tr>
                            <tr>
                                <td>Row 2 Col 1</td>
                                <td>Row 2 Col 2</td>
                                <td>Row 2 Col 3</td>
                                <td>Row 2 Col 4</td>
                                <td>Row 2 Col 5</td>
                                <td>Row 2 Col 6</td>
                                <td>Row 2 Col 7</td>
                                <td>Row 2 Col 8</td>
                            </tr>
                            <tr>
                                <td>Row 3 Col 1</td>
                                <td>Row 3 Col 2</td>
                                <td>Row 3 Col 3</td>
                                <td>Row 3 Col 4</td>
                                <td>Row 3 Col 5</td>
                                <td>Row 3 Col 6</td>
                                <td>Row 3 Col 7</td>
                                <td>Row 3 Col 8</td>
                            </tr>
                            <tr>
                                <td>Row 4 Col 1</td>
                                <td>Row 4 Col 2</td>
                                <td>Row 4 Col 3</td>
                                <td>Row 4 Col 4</td>
                                <td>Row 4 Col 5</td>
                                <td>Row 4 Col 6</td>
                                <td>Row 4 Col 7</td>
                                <td>Row 4 Col 8</td>
                            </tr>
                            <tr>
                                <td>Row 5 Col 1</td>
                                <td>Row 5 Col 2</td>
                                <td>Row 5 Col 3</td>
                                <td>Row 5 Col 4</td>
                                <td>Row 5 Col 5</td>
                                <td>Row 5 Col 6</td>
                                <td>Row 5 Col 7</td>
                                <td>Row 5 Col 8</td>
                            </tr>
                            <tr>
                                <td>Row 6 Col 1</td>
                                <td>Row 6 Col 2</td>
                                <td>Row 6 Col 3</td>
                                <td>Row 6 Col 4</td>
                                <td>Row 6 Col 5</td>
                                <td>Row 6 Col 6</td>
                                <td>Row 6 Col 7</td>
                                <td>Row 6 Col 8</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            `;
            $('#tablesContainer').append(newTable);
        })

        $('#saveTablesButton').click(function() {
            var tablesHtml = $('#tablesContainer').html();
            $.ajax({
                url: '/save-tables',
                type: 'POST',
                data: {
                    tables_html: tablesHtml
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    alert('Tables saved successfully!');
                },
                error: function(xhr, status, error) {
                    console.error(error);
                    alert('An error occurred while saving the tables.');
                }
            });
        });
    });
</script>
</body>
</html>