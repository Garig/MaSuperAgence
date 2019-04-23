import Places from 'places.js'
import Map from './modules/map'
// import './events1'
// import './events2'
import 'slick-carousel'
import 'slick-carousel/slick/slick.css'
import 'slick-carousel/slick/slick-theme.css'

Map.init();

let inputAddress = document.querySelector('#property_adress')
if (inputAddress !== null) {
  let place = Places({
    container: inputAddress
  })
  place.on('change', e => {//ES6
    document.querySelector('#property_city').value = e.suggestion.city
    document.querySelector('#property_postal_code').value = e.suggestion.postcode
    document.querySelector('#property_lat').value = e.suggestion.latlng.lat
    document.querySelector('#property_lng').value = e.suggestion.latlng.lng
  })
}

let searchAddress = document.querySelector('#search_adress')
if (searchAddress !== null) {
  let place = Places({
    container: searchAddress
  })
  place.on('change', e => {//ES6
    document.querySelector('#lat').value = e.suggestion.latlng.lat
    document.querySelector('#lng').value = e.suggestion.latlng.lng
  })
}

const $ = require('jquery');
require("fancybox");

require('../sass/app.scss');
require('../css/app.css');
require('../css/button.css');
require('../css/navbar.css');

require('select2');

console.log('Hello Webpack Encore! Edit me in assets/js/app.js');

// document.write('Hello la compagnie');

$('select').select2();
$('[data-slider]').slick({
  dots: true,
  arrows: true
});

$('.js-datepicker').datepicker({
    todayHighlight: true,
    format: 'dd/mm/yyyy'
});

//affichage du formulaire de contact et cacher le button
// var button = document.querySelector("#contactButton");
// button.addEventListener("click", function(){
//     e.preventDefault();
//     $("#contactForm").slideDown()
//     $("#contactButton").slideUp()
// });
$("#contactButton").click(e=>{//ES6
    e.preventDefault();
    $("#contactForm").slideDown();
    $("#contactButton").slideUp();//il aurait pu mettre this.slideUp() oui mais comme c est du $ non
});

// gestion de l'image à la une sur la page show.html.twig
let main = document.querySelector('.main')
let amain = document.querySelector('a.single_image')
let x = document.querySelectorAll(".thumb");
let i;
for (i = 0; i < x.length; i++) {//ES6
    x[i].addEventListener('click', (e)=>{//ici ça veut dire j ajoute un event sur les 3
        e.preventDefault()
        let target = e.target//je recupere l'élement cliqué
        let url = target.getAttribute('src')
        main.setAttribute('src', url)
        amain.setAttribute('href', url)
  });
}
// mais tu pouvais faire aussi comme ça pour la boucle
// let main = document.querySelector('.main')
// let amain = document.querySelector('a.single_image')
// let x = document.querySelectorAll(".thumb")
// x.forEach(thumb => {
//     thumb.addEventListener('click', (e)=>{
//         e.preventDefault()
//         let target = e.target//je recupere l'élement cliqué
//         let url = target.getAttribute('src')
//         main.setAttribute('src', url)
//         amain.setAttribute('href', url)
//     })
// });

// Fancybox
$(document).ready(function() {

	/* This is basic - uses default settings */
	
    $("a.single_image").fancybox();
	
	/* Using custom settings */
	
	$("a.inline").fancybox({
		'hideOnContentClick': true
	});

	/* Apply fancybox to multiple items */
	
	$("a.group").fancybox({
		'transitionIn'	:	'elastic',
		'transitionOut'	:	'elastic',
		'speedIn'		:	600, 
		'speedOut'		:	200, 
		'overlayShow'	:	false
    });
    
    $("a.iframe").fancybox({
        'width'             : '50%',
        'height'            : '50%',
        'autoScale'         : false,
        'transitionIn'      : 'elastic',
        'transitionOut'     : 'elastic',
        'type'              : 'iframe'
    });

    $("a.grouped_elements").fancybox();

	//suppression des éléments
document.querySelectorAll('[data-delete]').forEach(a => {
  a.addEventListener('click', e => {
      e.preventDefault()
      fetch(a.getAttribute('href'), {
          method: 'DELETE',
          headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Content-Type': 'application/json'
          },
          body: JSON.stringify({'_token': a.dataset.token})
      }).then(response => response.json())
        .then(data => {
              if(data.success) {
                  a.parentNode.parentNode.removeChild(a.parentNode)
                  alert('image supprimée')
              } else {
                alert(data.error)
              }
        })
        .catch(e=>alert(e))
  })
})

//ajout et suppression d'un like
function onClickBtnLike(event){
  event.preventDefault();

  const url = this.href;
  const spanCount = this.querySelector('span.js-likes');
  const icone = this.querySelector('i');

  axios.get(url).then(function(response){
      console.log(response)
      const likes = response.data.likes;
      spanCount.textContent = likes;

      if (icone.classList.contains('fas')){
          icone.classList.replace('fas','far');
      }else{
          icone.classList.replace('far','fas');
      }
  }).catch(function(error){
      console.log(error)
      if (error.response.status === 403) {
          window.alert("Vous ne pouvez pas liker un bien si vous n'êtes pas connecté !")
      } else {
          window.alert("Une erreur s'est produite, réessayez plus tard.")
      }
  });
}
document.querySelectorAll('a.js-like').forEach(function(link){
  link.addEventListener('click', onClickBtnLike);
})
});

