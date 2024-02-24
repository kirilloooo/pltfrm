<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Complaint Form</title><style>.centered-btn {  display: flex;  justify-content: center;}
  </style>
</head>
<body>

<div class="container mt-5">
  <div class="row justify-content-center"><div class="col-md-6">    <div class="centered-btn">        <button id="complaintBtn" class="btn btn-secondary">Complaint</button>    </div>  <div id="complaintForm" style="display: none;">    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">      <div class="form-group">        <label for="page">Page:</label>        <input type="text" class="form-control" id="page" name="page" readonly>      </div>      <div class="form-group">        <label for="fileid">ID:</label>        <input type="text" class="form-control" id="fileid" name="fileid" value="<?php echo $file['origin_file_id']; ?>" readonly>      </div>      <div class="form-group">        <label for="reason">Reason for complaint:</label>        <select class="form-control" id="reason" name="reason" required>          <option value="Spam">Spam</option>          <option value="Violence">Violence</option>          <option value="Child pornography">Child pornography</option>          <option value="Pornography">Pornography</option>          <option value="Copyrights">Copyrights</option>          <option value="Drugs">Drugs</option>          <option value="Personal data">Personal data</option>          <option value="Other">Other</option>          <!-- Добавьте другие варианты, если необходимо -->        </select>      </div>      <div id="additionalInfo" style="display: none;">        <div class="form-group">          <label for="additional">Additional Information:</label>          <textarea class="form-control" id="additional" name="additional"></textarea>        </div>      </div>      <input type="submit" class="btn btn-secondary" value="Submit a complaint">    </form>  </div>  <div id="thanksMessage" style="display: none;">    <p>Thank you for your help in dealing with <span id="complaintReason"></span></p>  </div></div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
  $(document).ready(function() {$("#complaintBtn").click(function() {  $("#complaintForm").show();  $("#complaintBtn").hide();});
// Получение текущего URL и вставка в поле "page"$("#page").val(window.location.href);$("#page").prop('readonly', true);
$("#reason").change(function() {  var selectedOption = $(this).val();  if (selectedOption === "Other" || selectedOption === "Personal data" || selectedOption === "Copyrights") {    $("#additionalInfo").show();  } else {    $("#additionalInfo").hide();  }});
$("form").submit(function(event) {  event.preventDefault();  var page = $("#page").val();  var reason = $("#reason").val();  var additional = $("#additional").val();  var fileid = $("#fileid").val();    // AJAX отправка данных на сервер  $.ajax({    type: 'POST',    url: $(this).attr('action'),    data: $(this).serialize(),    success: function() {      // Отправка данных в телеграм-бота      var telegram_message = "New complaint:\nPage: " + page + "\nFile ID: " + fileid + "\nReason: " + reason + "\nAdditional Information: " + additional;      $.post("report_method.php", { message17: telegram_message }, function(response) {        console.log(response); // Если нужно что-то сделать с ответом      });
      // Показ благодарности      $("#complaintForm").hide();      $("#thanksMessage").show();      $("#complaintReason").text(reason);    }  });});
  });
</script>

</body>
</html>
