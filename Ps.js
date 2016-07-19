function postListString() {
        $.ajax({
            type: "POST",
            url: "read.php",
            contentType: "application/x-www-form-urlencoded",
            dataType: "json",
            data: {act:'ajax'},
            success: function (jsonResult) {
                console.log(jsonResult);
            }
        });
		waiting()
    }
//等待服务器回答
function waiting() {
    //clearTimeout(response_tout);
    response_tout = setTimeout('welive()', 5000)
}
//关键ajax等待
function welive() {
    console.log(123);
	postListString()
    //ajax("read.php")
}