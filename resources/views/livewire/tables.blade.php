<div x-data="{ selectedcolor: null}">
<style>
    table {
        width: 100%;
        margin-bottom: 20px;
    }
    th, td {
        text-align: center!important;
        vertical-align: middle!important;
        height: 35px;
        width: 35px;
        font-weight: 600;
        min-width: 25px;
        font-size: 23px;
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
    /* Styling for the divider cells in the first column */
    .divider-cell {
        position: relative; /* Ensure positioning context */
    }

    .vertical-red-line::before {
        content: "";
        position: absolute;
        top: 0;
        bottom: 0;
        left: 50%;
        border-left: 2px solid red; /* Divider line style */
    }

    .horizon-red-line::before {
        content: "";
        position: absolute;
        left: 0;
        right: 0;
        top: 50%;
        border-top: 2px solid red; /* Divider line style */
    }

    @media screen and (max-width : 430px) {
        th, td {
            font-size: 13.2px;
        }
        .col-75 {
            width: 77%!important;
            flex-basis: unset!important;
        }
        .col-25 {
            width: 24%!important;
            flex-basis: unset!important;
        }
        .box-table-flex{
            flex-direction: column-reverse!important;
            flex-flow: column;
        }
        .table-box {
            width: 100%!important;
        }
        th, td {
            text-align: center!important;
            vertical-align: middle!important;
            height: 21px;
            width: 21px;
            min-width: unset;
            font-weight: 600;
            font-size: 13.2px;
            padding: 0!important;
        }
    }

    .box-table-flex {
        display: flex;
        width: 100%;
        justify-content: space-between;
        margin-top:150px
    }
    .table-box {
        width: 48%;
    }
</style>
<script>
    function debouncedSearch() {
        console.log('Searched')
        // @this.search();
        //
        // let timeout;
        // clearTimeout(timeout);
        // timeout = setTimeout(() => {
        //     @this.search();
        // }, 1500);
}
</script>
    <div class="fixed-header">
        <div class="container">
            <div class="row">
                <div class="col col-75" >
                    <button  wire:loading.attr="disabled" x-on:click="$wire.fillColor('red')" id="redButton" class="btn btn-danger">Red</button>
                    <button  wire:loading.attr="disabled" x-on:click="$wire.fillColor('green')" id="greenButton" class="btn btn-success">Green</button>
                    <button  wire:loading.attr="disabled" x-on:click="$wire.fillColor('blue')" id="blueButton" class="btn btn-primary">Blue</button>
                    <button  wire:loading.attr="disabled" x-on:click="$wire.undoFilledCell()" class="btn btn-primary">Undo</button>
                    <button  wire:loading.attr="disabled" x-on:click="$wire.resetTable()" class="btn btn-primary">Reset color</button>
                </div>
                <div class="col col-25">
                    <button x-on:click="$wire.addTable()" class="btn btn-warning">Add Table</button>
                    <span>{{$totalTables}} Tables</span>
{{--                    <button wire:loading.attr="disabled" wire:click="search" id="findTableButton" class="btn btn-secondary">Find Matching Table</button>--}}
                    <div wire:loading>
                        Finding ...
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="box-table-flex">
        <div class="table-box">
            {{ $tables->links() }}
            <div id="tablesContainer">
                @foreach($tables as $table)
{{--                    <button x-on:click="$wire.expandTable({{$table->id}})" class="btn btn-warning">Expand</button>--}}
{{--                    <button x-on:click="$wire.collapseTable({{$table->id}})" class="btn btn-danger">Collapse</button>--}}
                    <button x-on:click="$wire.deleteTable({{$table->id}})" class="btn btn-danger">Delete</button>
                    <div class="table-responsive">
                        <table class="table table-bordered" style="width: unset!important">
                            @foreach($table->rows as $keyRow => $rows)
                                <tr>
                                    @foreach($rows as $cell)
                                        <td
                                            style="color: {{$cell->color ?? 'white'}}; position: relative"
                                            class="
                                                @if(isset($table->verticalColumns[$cell->column]) && !$cell->vertical_column) vertical-red-line @endif
                                                @if($cell->vertical_column) horizon-red-line @endif"
                                        >
                                            {{$cell->symbol}}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </table>
                    </div>
                @endforeach
            </div>
            @if ($tables->isEmpty())
                <div class="alert alert-warning">Don't match with any tables</div>
            @endif
        </div>
        <div class="table-box">
                @if ($searchTable)
                    <button x-on:click="$wire.fillSearchColor('red');debouncedSearch()"
                            wire:loading.attr="disabled"
                            id="redButton" class="btn btn-danger">Red</button>
                    <button x-on:click="$wire.fillSearchColor('green');debouncedSearch()" id="greenButton"
                            wire:loading.attr="disabled"
                            class="btn btn-success">Green</button>
                    <button x-on:click="$wire.fillSearchColor('blue');debouncedSearch()" id="blueButton"
                            wire:loading.attr="disabled"
                            class="btn btn-primary">Blue</button>
                    <button x-on:click="$wire.resetSearchTable();debouncedSearch()"  class="btn btn-primary">Reset</button>
                    <button  wire:loading.attr="disabled" x-on:click="@this.search()"
                             class="btn btn-secondary">Search</button>

                    @if ($this->stringSearch)
                        <span>{{count($this->searchIds)}} Tables matching</span>
                    @endif
                    <div class="table-responsive">
{{--                        <button x-on:click="$wire.expandSearchTable({{$searchTable->id}})" class="btn btn-warning">Expand</button>--}}
{{--                        <button x-on:click="$wire.collapseSearchTable({{$searchTable->id}})" class="btn btn-danger">Collapse</button>--}}
                        <table class="table table-bordered" style="width: unset!important; background-color:#df9a9a14">
                            @foreach($searchTable->rows as $keyRow => $rows)
                                <tr>
                                    @foreach($rows as $cell)
                                        <td
                                            style="color: {{$cell->color ?? 'white'}}; position: relative"
                                            class="
                                                @if(isset($searchTable->verticalColumns[$cell->column]) && !$cell->vertical_column) vertical-red-line @endif
                                                @if($cell->vertical_column) horizon-red-line @endif"
                                        >
                                            {{$cell->symbol}}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </table>
                    </div>
                @else
                    <button x-on:click="$wire.addSearchTable()" class="btn btn-warning">Add Search Table</button>
               @endIf
        </div>
    </div>

</div>
