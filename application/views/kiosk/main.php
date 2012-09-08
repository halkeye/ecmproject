<script type="text/javascript">
var lookupURL = "<?php echo URL::site('/kiosk/lookupReg') ?>";
var spinnerImage = "<?php echo URL::site('/static/spinner/spinner-large.gif', NULL, FALSE) ?>";
</script>
<script type="text/javascript" src="<?php echo URL::site('/static/js/llqrcode.js',NULL,FALSE) ?>" /></script>
<script type="text/javascript" src="<?php echo URL::site('/static/js/webqr.js',NULL,FALSE) ?>" /></script>
<script type="text/javascript" src="<?php echo URL::site('/static/spinner/jquery.spinner.js',NULL,FALSE) ?>" /></script>

<style type="text/css"><!--
#qr-canvas { border: 1px solid black; display: none; }
#outdiv { height: 300px; width: 400px; }
/* undo css */
#content table, #content table td { border: 0 }

#kiosk_lookup_form input { width: 80px !important; }
#kiosk_lookup .fieldHeader {
	margin: 5px -10px;
	padding: 7px 10px;
	font-weight: bold;
	font-size: 14px;
	color: #000;
	background-color: #DDD;	
	text-decoration: underline;
}
.notPaid { 
    color: white;
    background-color: #F00;
}
.alreadyPickedUp { 
    color: white;
    background-color: #F00;
}
--></style>

<table id="kiosk_table">
    <tr>
        <td>
            Lookup:<br/>
            <form id="kiosk_lookup_form">
            <input type="text" id="kiosk_lookup_text" size="14">
            <input type="submit" value="search" />
            </form>
            <br/>
            Scan:<br/>
            <video id="v" autoplay></video>
        </td>
        <td>
            <div  id="kiosk_lookup">
                <table>
                    <tr>
                        <td class="fieldHeader">Name</td>
                        <td id="lookup_name"></td>
                    </tr>
                    <tr>
                        <td class="fieldHeader">Reg Id</td>
                        <td id="lookup_reg_id"></td>
                    </tr>
                    <tr>
                        <td class="fieldHeader">Pass</td>
                        <td id="lookup_pass"></td>
                    </tr>
                    <tr>
                        <td class="fieldHeader">Convention</td>
                        <td id="lookup_convention"></td>
                    </tr>
                    <tr>
                        <td class="fieldHeader">Status</td>
                        <td id="lookup_status"></td>
                    </tr>
                    <tr>
                        <td class="fieldHeader">Picked Up Already</td>
                        <td id="lookup_pickup_status"></td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>
</table>

<canvas id="qr-canvas" width="400" height="300"></canvas>

