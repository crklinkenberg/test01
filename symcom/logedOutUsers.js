
//Sign in with email and password
$('#signinBtn').on('click', function (e) {
  alert('hello');
  e.preventDefault();
    var request = $.ajax({
      url: "http://alegralabs.com/hemanta/symcom/api/public/v1/autor/all",
      headers: {
        "Authorization" : "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImFlOGJkMWVjMGU2ZjEzZDI5NDhlNmQ2YjQyZGQ0MDA5MGMwMjk0OWEyZTliYjM1YjUzNWZiMjUyYWZjY2JmZTAzNWZlOGRhZDQ0ODhiMWZmIn0.eyJhdWQiOiIyIiwianRpIjoiYWU4YmQxZWMwZTZmMTNkMjk0OGU2ZDZiNDJkZDQwMDkwYzAyOTQ5YTJlOWJiMzViNTM1ZmIyNTJhZmNjYmZlMDM1ZmU4ZGFkNDQ4OGIxZmYiLCJpYXQiOjE1Mzg2NDUyMzQsIm5iZiI6MTUzODY0NTIzNCwiZXhwIjoxNTcwMTgxMjM0LCJzdWIiOiIxIiwic2NvcGVzIjpbIioiXX0.TD6-FmgI_tX3S-UYWlS2Uz8uxCtgIqpCb2g-cX4RwggLbuFNz6nc-GZF80NO3VUkSVNXhf4QLF-whZvHwjfPuXxgLKr7Zk_tNZcFKb2uLsH08STim9GjiPXg5b9rHgk8rytP7n5tn8liYFDg2_8GrZIAHwG8Fhm9UwVsb4irUV0jEIBE3j61mOo_LTbWw-ut15hF6_zfsoYNCpR1nxItRjqo_AxjLk5e99hNCi0ox8B2zVTyLn8C2y0j5S3rAlAfI6kZ6h5LUof-y4BXhGCTr_1UZ6uokTPlBOnh1MY6AeBANWcy_RxkQb_jiqx-4I_HE__SB4hem5T0jC__yh5EXPGwmgFaz9o-DlISokcgwbCS_BJaDwkArz226cTUm6NmKVYclwCi9H9dspGJlhS9g2vRgMi80N2Jzntt0Q87EfsHkdG1OxTdYurE74sF_CM_u_QFTAdtoPxJCmEe0Arqdlv9jzsjUa85kLQrYp6qQq6cBnecQzsRbmEGiqkOEe3BwlvFFrY-gnd1oMvviXhvz5cqzEth_v6ODlDWEZvmvzYr3oSa1PCZe7Lm3h3tCLvhB9nCRG6hpwNAj1O4aYUOVxZXtrycsR2cusOIPhlxI77Md3h1JSOK5g5Y2reBHe5Ua3jjEWwdiBQ6qL79GaXyxyTcKw-XjhoxKGg-lm2_drw"
      },
      type: "GET",
      // data: {
      //   'username' : 'harry',
      //   'password' : 'guest!@#'
      // },
      beforeSend:function(){
      },
      complete:function(jqXHR, status){
        
      }
    });
    request.done(function(response) {
      var userData = null;
      try {
          userDate = JSON.parse(response); 
      } catch (e) {
          userData = response;
      }
      console.log(userData);
    });

    request.fail(function(jqXHR, textStatus) {
      var errorData = null;
      try {
          errorData = JSON.parse(jqXHR); 
      } catch (e) {
          errorData = jqXHR;
      }
      console.log(errorData);
      
    });
});

