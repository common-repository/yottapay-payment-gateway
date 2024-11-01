// Authorize | Process authorize
function yottapayProcessAuthorize() {
    try {
        //Hide yottapay login button
        var boxYottaPayLogIn = document.getElementById('boxYottaPayLogIn');

        if (boxYottaPayLogIn != null) {
            boxYottaPayLogIn.style.display = 'none';
        }

        //Call authorize controller
        fetch('/wc-api/yottapay_authorize', {method: 'POST'})
            .then((response) => {
                if (response.ok) {
                    return response.json();
                } else {
                    throw 'Check internet connection and try again.';
                }
            }).then((data) => {
                console.log(data);
                if (data.status == '1') {
                    //Navigate to authorize page
                    window.location.href = data.link;
                } else {
                    alert(data.error);
                    //Show yottapay login button
                    if (boxYottaPayLogIn != null) {
                        boxYottaPayLogIn.style.display = 'block';
                    }
                }
            })
            .catch((promiseError) => {
                console.log(promiseError);
                alert('Request failed.');
                //Show yottapay login button
                if (boxYottaPayLogIn != null) {
                    boxYottaPayLogIn.style.display = 'block';
                }
            });
    } catch (e) {
        console.log(e);
        alert('Request failed.');
        //Show yottapay login button
        var boxYottaPayLogIn = document.getElementById('boxYottaPayLogIn');
        if (boxYottaPayLogIn != null) {
            boxYottaPayLogIn.style.display = 'block';
        }
    }
}