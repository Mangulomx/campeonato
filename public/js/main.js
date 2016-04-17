

$.validator.setDefaults({
   errorClass: 'form_error',
   errorElement: 'div'
});

$.validator.addMethod("dniCheck", function(value, element){
    var valor = false;
    if(/^([0-9]{8})*[a-zA-Z]+$/.test(value)){
        var numero = value.substr(0, value.length-1);
        var let = value.substr(value.length-1,1).toUpperCase();
        console.log("let"+let);
        numero = numero % 23;
        var letra = 'TRWAGMYFPDXBNJZSQVHLCKET';
        letra = letra.substring(numero,numero+1);
        console.log("letra"+letra);
        if(letra==let){ 
            valor = true;
            console.log("valor"+valor);
        }
    }
    return valor;
},"DNI no válido");
$("#form_user").validate({
   rules:{
       inputuser:{
           required: true,
           rangelength: [3,10]
       },
       inputpassword:{
           required: true
       },
       inputpassword1:{
           required: true,
           equalTo: "#inputpassword"
       },
       inputemail:{
           required: true,
           email: true
       }
   },
   messages:{
       inputuser: {
           required : "No puede dejar el campo usuario vacio",
           rangelength: "Porfavoz, introduce un valor entre 3 y 10 caracteres de longitud"
       },
       inputpassword: {
           required: "No puede dejar el campo vacio"
       },
       inputpassword1: {
           required: "No puede dejar este campo vacio",
           equalTo: "Introduce la misma contraseña otra vez"
       },
       inputemail: {
           required: "El campo email no puede dejarlo vacio",
           email: "Porfavoz, introduce un email valido"
           
       }
   },
   success: function(element){
       element.remove();
   }
});

$("#form_participante").validate({
   rules:{
       nieP:{
           dniCheck: true
       },
       nombreP:{
           required: true
       },
       apellidoP:{
           required: true
       },
       provinciaP:{
           required: true
       },
       localidadP:{
           required: true
       }
   },
   messages:{
       nieP:{
           dniCheck:"Introduce el dni correcto"
       },
       nombreP:{
           required: "El campo nombre no puede estar vacio"
       },
       apellidoP:{
           required: "El campo apellidos no puede estar vacio"
       },
       provinciaP:{
           required: "El campo provincia no puede estar vacio"
       },
       localidadP:{
           required: "El campo localidad no puede estar vacio"
       }
               
   },
   success: function(element){
    element.remove();
    }
});

function getcheckBoxValues()
{
   

    var valores=[];
    var inputs = document.getElementsByTagName('input');
    sw = false;
    for(var i=0; i<inputs.length; i++)
    {
        if(inputs[i].checked)
        {
            valores.push(inputs[i].value);
            sw = true;
        }
    }
    if(!sw)
    {
        alert("Tienes que seleccionar los campos que quieres eliminar");
        sw = false;
    }
    else
    {
        $subcad = (valores.length === 1) ? "el" : "los";
        $subcad1 = (valores.length>1) ? "es ":" ";
        return confirm("Quieres eliminar "+$subcad+" indentificador"+$subcad1+""+valores.join(" , "));
    } 
    return sw; 
}
