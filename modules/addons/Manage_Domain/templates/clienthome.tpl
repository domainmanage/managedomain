<div class="row">
    <div class="col-md-3 pull-md-right sidebar">
        <div menuitemname="Add Funds" class="panel panel-sidebar panel panel-info">
            <div class="panel-heading">
                <h3 class="panel-title">
                    واریز
                    <i class="fas fa-chevron-up panel-minimise pull-left"></i>
                </h3>
            </div>
            <div class="panel-body">
                {$lang["chargedescription"]}
            </div>
        </div>
    </div>
    <div class="col-md-9 pull-md-left main-content">
        <div class="row">
            <div class="col-sm-8 col-sm-offset-2">
                {if $status  eq "error" && $type eq 'pricetype'}
                    <div class="alert alert-danger alert-dismissible =" role="alert">
                        {$lang["firsttype"]}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                {/if}
                {if $status  eq "success"}
                    <div class="alert alert-success alert-dismissible =" role="alert">
                        {$lang["sharjsuccessfullmessgae"]}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                {/if}
                {if $status  eq "error" and $balance eq 'low'}
                    <div class="alert alert-danger alert-dismissible =" role="alert">
                        <strong>{$lang["error"]}</strong>{$lang["lowbalance"]}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                {/if}
                {if $status  eq "error" and $balance eq 'intval'}
                    <div class="alert alert-danger alert-dismissible =" role="alert">
                        <strong>{$lang["error"]}</strong>{$lang["numberfeild"]}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                {/if}

                {if $status  eq "error" and $balance eq 'notrefund'}
                    <div class="alert alert-danger alert-dismissible =" role="alert">
                        <strong>{$lang["error"]}</strong>{$lang["refundmessage"]}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                {/if}


                <div class="panel">
                    <table class="table table-striped">
                        <tbody>
                        <tr>
                            <td class="textright"><strong>{$lang["balance"]} : </strong></td>
                            <td class="currency">{$userBalance|number_format:2}</td>
                        </tr>
                        <tr>
                            <td class="textright"><strong>{$lang["youremail"]}: </strong></td>
                            <td> {$email} </td>
                        </tr>

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-sm-6 col-sm-offset-3">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <form method="get" action="index.php?m=Manage_Domain">
                            <input type="hidden" name="m" value="Manage_Domain"">
                            <fieldset>
                                <div class="form-group">
                                    <label for="amount" class="control-label">{$lang["payprice"]} :</label>
                                    <input type="text" name="amount" placeholder="{$lang["inputprice"]}"
                                           class="form-control currency" required="">
                                </div>
                                <div class="form-group">
                                    <label for="amount" class="control-label">{$lang["email"]}: </label>
                                    <input type="text" name="email" id="email" value="{$email}" class="form-control"
                                           required="">
                                </div>

                                <div class="form-group">
                                    <input type="submit" value="واریز" class="btn btn-primary btn-block"
                                           {if $status  eq "error" && $type eq 'pricetype'}disabled{/if}>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                    <div class="panel-footer">
                        * {$lang["refundsharj"]}

                    </div>
                </div>
            </div>
        </div>
    </div>

</div>