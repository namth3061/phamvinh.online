<div x-data="{ selectedcolor: null}">
<style>
    table {
        width: 100%;
        margin-bottom: 20px;
    }
    th, td {
        text-align: center!important;
        vertical-align: middle!important;
        height: 50px;
        width: 50px;
        min-width: 25px;
        font-weight: 600;
        font-size: 28px;
        padding: 0!important;
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
    .container.mt-5 {
        margin-top: 8rem!important;
    }
    .active {
        background: #ffff002e;
    }
    button {
        margin-bottom: 5px;
    }
    @media screen and (max-width : 430px) {
        th, td {
        font-size: 20px;
    }
    }
</style>
    <div class="fixed-header">
        <div class="container">
            <div class="row">
                <div class="col" >
                    <button x-on:click="selectedcolor = 'red'"id="redButton" class="btn btn-danger">Red</button>
                    <button x-on:click="selectedcolor = 'green'" id="greenButton" class="btn btn-success">Green</button>
                    <button x-on:click="selectedcolor = 'blue'" id="blueButton" class="btn btn-primary">Blue</button>
                    <button x-on:click="selectedcolor = null" class="btn btn-primary">Reset color</button>
                    <button x-on:click="$wire.addTable()" class="btn btn-warning">Add Table</button>
                </div>
                <div class="col">
                    <input type="text" wire:model="columnsIndex" id="searchInput" class="form-control" placeholder="1 2 3 4 5 6 7 8">
                    <button wire:loading.attr="disabled" wire:click="search"id="findTableButton" class="btn btn-secondary mt-2">Find Matching Table</button>
                    <div wire:loading>
                        Finding ...
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container mt-5">
        <div id="tablesContainer">
            @foreach($tables as $table)
            <button x-on:click="$wire.expandTable({{$table->id}})" class="btn btn-warning">Expand</button>
            <button x-on:click="$wire.collapseTable({{$table->id}})" class="btn btn-danger">Collapse</button>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        @foreach($table->rows as $keyRow => $rows)
                            <tr>
                                @foreach($rows as $cell)
                                    <td x-on:click="$wire.fillColor({{$table->id}},selectedcolor, {{$keyRow}}, {{$cell->column}})" style="color: {{$cell->color ?? 'white'}};">{{$cell->symbol}}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </table>
                </div>
            @endforeach
            {{ $tables->links() }}
        </div>
        @if ($tables->isEmpty())
            <div class="alert alert-warning">Don't match with any tables</div>
        @endif
    </div>

</div>
