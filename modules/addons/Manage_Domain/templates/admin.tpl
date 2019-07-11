<script !src="">
    function checkAll(ele) {
        var checkboxes = document.getElementsByTagName('input');
        if (ele.checked) {
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].type == 'checkbox') {
                    checkboxes[i].checked = true;
                }
            }
        } else {
            for (var i = 0; i < checkboxes.length; i++) {
                console.log(i)
                if (checkboxes[i].type == 'checkbox') {
                    checkboxes[i].checked = false;
                }
            }
        }
    }
</script>

<style>
    input, select, textarea {
        font-family: Tahoma;
        font-size: 11px;
    }

    .table-fixed {
        width: 100%;
        background-color: #f3f3f3;

    tbody {
        height: 200px;
        overflow-y: auto;
        width: 100%;
    }

    thead, tbody, tr, td, th {
        display: block;
    }

    tbody {

    td {
        float: left;
    }

    }
    thead {

    tr {

    th {
        float: left;
        background-color: #f39c12;
        border-color: #e67e22;
    }

    }
    }
    }


    .managedomain .head {
        padding: 10px 25px 10px 25px;
        background-color: #666;
        font-weight: bold;
        font-size: 14px;
        color: #E3F0FD;
        margin: 0 0 15px 0;
        -moz-border-radius: 5px;
        -webkit-border-radius: 5px;
        -o-border-radius: 5px;
        border-radius: 5px;
    }
</style>

<ul class="nav nav-tabs admin-tabs" role="tablist">
    <li class="active">
        <a class="tab-top" href="#tab1" role="tab" data-toggle="tab" id="tabLink1" data-tab-id="1" aria-expanded="true">Transaction</a>
    </li>
    <li class="">
        <a class="tab-top" href="#tab3" role="tab" data-toggle="tab" id="tabLink3" data-tab-id="3" aria-expanded="true">Fast
            Importer</a>
    </li>

    <li class="">
        <a class="tab-top" href="#tab2" role="tab" data-toggle="tab" id="tabLink2" data-tab-id="2"
           aria-expanded="false">Setting</a>
    </li>
</ul>
<div class="tab-content admin-tabs">
    <div class="tab-pane active" id="tab1">
        <table id="sortabletbl1" class="datatable text-center" width="100%" border="0" cellspacing="1" cellpadding="3">
            <tbody>
            <tr class="bg-color-pri">
                <th>#</th>
                <th>Amount</th>
                <th>User</th>
                <th>Invoice</th>
                <th>Description</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
            {foreach $transactions as $index=>$transaction}
                <tr>
                    <td>{($index+1)}</td>
                    <td>{$transaction->amount}</td>
                    <td>
                        <a href="clientssummary.php?userid={$transaction->userid}">{$transaction->firstname} {$transaction->lastname}</a>
                    </td>
                    <td>
                        <a href="invoices.php?action=edit&id={$transaction->invoiceid}"> {$transaction->invoiceid}</a>
                    </td>
                    <td>{$transaction->description}</td>
                    <td>
                        {if $transaction->status eq payed}
                            <span class="label label-success"> {$transaction->status} </span>
                        {else}
                            <span class="label label-success"> {$transaction->status} </span>
                        {/if}
                    </td>
                    <td>{$transaction->date}</td>
                </tr>
            {/foreach}
            <tr>
                <td COLSPAN="10" class="text-center">
                    <ul class="pagination">
                        <li {if $currentpage lt 2}class="disabled" {/if}>
                            <a href="addonmodules.php?module=Manage_Domain&page={if $currentpage lte 1}{$currentpage}{else}{$currentpage -1}{/if}">Previous</a>
                        </li>

                        {for $i=1 to $pagenumber}
                            <li {if $currentpage eq $i}class="active" {/if}><a
                                        href="addonmodules.php?module=Manage_Domain&page={$i}">{$i}</a></li>
                        {/for}
                        <li {if $currentpage eq $pagenumber}class="disabled" {/if}><a
                                    href="addonmodules.php?module=Manage_Domain&page={if $currentpage lte $pagenumber-1}{$currentpage + 1}{else}{$currentpage }{/if}">next</a>
                        </li>
                    </ul>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="tab-pane" id="tab2">
        <div class="row">
            <fieldset class="fieldset col-md-3">
                <legend></legend>
                <form action="addonmodules.php" method="GET">
                    <input type="hidden" name="module" value="Manage_Domain">
                    <div class="form-group">
                        <span>price type: &nbsp;</span>
                        <label class="checkbox-inline">
                            <input type="radio" name="pricetype" value="toman" {if $pricetype eq 'toman'}checked{/if} >toman</label>
                        <label class="checkbox-inline">
                            <input type="radio" name="pricetype" value="rial" {if $pricetype eq 'rial'}checked{/if}>rial
                        </label>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn btn-success" value="submit">
                    </div>
                </form>
            </fieldset>
        </div>
    </div>

    <div class="tab-pane" id="tab3">
        <div class="row">
            <div class="col-md-12">
                <div class="managedomain">
                    <div class="head">Fast importer</div>
                    <div class="infobox">
                        <strong><span class="title">Fast importer</span></strong>
                        <br>
                        You can add all products with one click then you need to modify product name and description.
                        Exchange rate is by rials, you need to modify that if you are using toman.
                    </div>
                    <form action="addonmodules.php?module=Manage_Domain&savefastimporter=1" method="post">
                        <table class="form" width="100%">
                            <tbody>

                            <tr>
                                <td class="fieldlabel" style="font-size: 13px">Pricechange Percent</td>
                                <td class="fieldarea">
                                    <input type="text" class="form-control" aria-describedby="pricechangehelp"
                                           name="changepercent" value="{$fastimportersetting.extrapercent}">
                                    <small id="pricechangehelp" class="form-text text-muted">A Help About this field in
                                        For increasing your balance automatically, set this option. Pay attention this
                                        action is based on percentage.
                                    </small>

                                </td>
                            </tr>

                            <tr>
                                <td class="fieldlabel" style="font-size: 13px">Exchange rate</td>
                                <td class="fieldarea">
                                    <input type="text" class="form-control" name="rounded" value="{$pricelist['usd']}"
                                           aria-describedby="exchangerate"
                                           readonly>
                                    <small id="exchangerate" class="form-text text-muted">
                                        currency rate is based on USD to Rial in the moment
                                    </small>
                                </td>
                            </tr>

                            <tr>
                                <td class="fieldlabel" style="font-size: 13px">Currency</td>
                                <td class="fieldarea">
                                    <select name="defaultcurrency" class="form-control"
                                            aria-describedby="systemcurrency">
                                        {foreach $currencies as $currencie}
                                            <option value="{$currencie->id}" {($currencie->default == $fastimportersetting.defaultcurrency) ? "selected":""}>{$currencie->code}</option>
                                        {/foreach}
                                    </select>
                                    <small id="systemcurrency" class="form-text text-muted">
                                        select your system currency rate
                                    </small>
                                </td>
                            </tr>

                            <tr>
                                <td class="fieldlabel" style="font-size: 13px">Convert Rate</td>
                                <td class="fieldarea">

                                    <select name="convertcurrency" class="form-control" aria-describedby="roundedhelp">
                                        <option value="1" {($fastimportersetting.convertrate == 1 ) ? "selected":""} >
                                            ریال
                                        </option>
                                        <option value="2"{($fastimportersetting.convertrate == 2 ) ? "selected":""} >
                                            تومان
                                        </option>
                                        <option value="3"{($fastimportersetting.convertrate == 3 ) ? "selected":""} >USD
                                        </option>
                                    </select>

                                    <small id="roundedhelp" class="form-text text-muted">
                                        all the appeared prices in the below list are based on Rial, you can select your
                                        currency based on your system
                                    </small>
                                </td>
                            </tr>

                            </tbody>
                        </table>
                        <div class="btn-container">
                            <button class="btn btn-success">Save automatic Setting</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-12">
                <form action="addonmodules.php?module=Manage_Domain&saveform=1" method="post">
                    <table class="table table-responsive table-striped">
                        <thead>
                        <tr>
                            <th><input type="checkbox" onchange="checkAll(this)" name="chk[]"/>
                            </th>
                            <th>TLD</th>
                            <th>Register (Rial)</th>
                            <th>Transfer (Rial)</th>
                            <th>Renew (Rial)</th>
                            <th>Restore (Rial)</th>
                        </tr>
                        </thead>
                        {foreach $pricelist as $key => $value}
                            {if $key ne 'usd'}
                                {if in_array($key,$tldsHistory)}
                                    <tr class="success">
                                        <td><input type="checkbox" name="{$key}" value="{$key}" checked></td>
                                        {else}
                                    <tr>
                                    <td><input type="checkbox" name="{$key}" value="{$key}"></td>
                                {/if}
                                <td>{$key}</td>
                                {if $value.exchange == 'usd'}
                                    <td>{($value.register * $pricelist['usd'])+1000|round:-3} </td>
                                    <td>{((isset($value.transfer))?($value.transfer * $pricelist['usd'])+1000:0)|round:-3} </td>
                                    <td>{((isset($value.renew))?($value.renew * $pricelist['usd'])+1000: 0)|round:-3} </td>
                                    <td>{((isset($value.restore))?($value.restore * $pricelist['usd'])+1000:0)|round:-3} </td>
                                {else}
                                    <td>{$value.register} </td>
                                    <td>{$value.transfer} </td>
                                    <td>{$value.renew} </td>
                                    <td>{$value.restore} </td>
                                {/if}
                                </tr>
                            {/if}
                        {/foreach}
                        <div class="pull-right">
                            <button type="submit" class="btn btn-primary"> Import Or Update Prices</button>
                        </div>
                    </table>
                    <div class="pull-right">
                        <button type="submit" class="btn btn-primary"> Import Or Update Prices</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>