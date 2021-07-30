<!-- 
    ######
    # THIS FILE IS ONLY AN EXAMPLE. PLEASE MODIFY AS REQUIRED.
    # Contributor: Md. Rakibul Islam <rakibul.islam@sslwireless.com>
    ######
 -->

 <!DOCTYPE html>

<head>
    <meta name="author" content="SSLCommerz">
    <title>Transaction Failed - SSLCommerz</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
</head>

<body>
    <div class="container">
        <div class="row" style="margin-top: 10%;">
            <div class="col-md-8 offset-md-2">
                <?php
                // First check if the POST request is real!
                if (empty($_POST['tran_id']) || empty($_POST['status'])) {
                    echo '<h2 class="text-center text-danger">Invalid Information.</h2>';
                    exit;
                }

                // Connect to database after confirming the request

                $tran_id = trim($_POST['tran_id']);

                if ($_POST['status'] == 'PENDING' || $_POST['status'] == 'FAILED') :
                

                ?>
                        <h2 class="text-center text-danger">Unfortunately your Transaction FAILED.</h2>
                        <br>

                        <table border="1" class="table table-striped">
                            <thead class="thead-dark">
                                <tr class="text-center">
                                    <th colspan="2">Payment Details</th>
                                </tr>
                            </thead>
                            <tr>
                                <td class="text-right">Error</td>
                                <td><?php echo $_POST['error'] ?></td>
                            </tr>
                            <tr>
                                <td class="text-right">Transaction ID</td>
                                <td><?php echo $_POST['tran_id'] ?></td>
                            </tr>
                            <tr>
                                <td class="text-right">Payment Method</td>
                                <td><?php echo $_POST['card_issuer'] ?></td>
                            </tr>
                            <?php if($_POST['bank_tran_id']){?>
<tr>
<td class="text-right">Bank Transaction Id</td>
<td><?php echo $_POST['bank_tran_id']  ?></td>
</tr>
                            <?php }
                            ?>
                            
                            <tr>
                                <td class="text-right"><b>Amount: </b></td>
                                <td><?php echo $_POST['amount'] . ' ' . $_POST['currency'] ?></td>
                            </tr>
                        </table>
                        <h2 class="text-center text-danger">Error updating record: </h2> <?php echo $_POST['error']; ?>
                    <?php endif; ?>
                <?php if ($_POST['status'] == 'PROCESSING') : ?>
                    <table border="1" class="table table-striped">
                        <thead class="thead-dark">
                            <tr class="text-center">
                                <th colspan="2">Payment Details</th>
                            </tr>
                        </thead>
                        <tr>
                            <td class="text-right">Transaction ID</td>
                            <td><?= $_POST['tran_id'] ?></td>
                        </tr>
                        <tr>
                            <td class="text-right">Transaction Time</td>
                            <td><?= $_POST['tran_date'] ?></td>
                        </tr>
                        <tr>
                            <td class="text-right">Payment Method</td>
                            <td><?= $_POST['card_issuer'] ?></td>
                        </tr>
                        <tr>
                            <td class="text-right">Bank Transaction ID</td>
                            <td><?= $_POST['bank_tran_id'] ?></td>
                        </tr>
                        <tr>
                            <td class="text-right">Amount</td>
                            <td><?= $_POST['amount'] . ' ' . $_POST['currency'] ?></td>
                        </tr>
                    </table>
                <?php endif ?>
            </div>
        </div>
    </div>
</body>