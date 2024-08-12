const btnCancelDeleteAddress = document.querySelector('.btnCancelDeleteAddress')
const overlayConfirmDeleteAddress = document.querySelector('.overlayConfirmDeleteAddress')
const btnConfirmDeleteAddress = document.querySelector('.btnConfirmDeleteAddress')
const allBtnDeletedAddress = document.querySelectorAll('button[data-deleted]');



allBtnDeletedAddress.forEach(btn => {
   btn.addEventListener('click', (e) => {
      window.scrollTo(0, 0);
      overlayConfirmDeleteAddress.style.display="block";
      document.body.style.overflow = 'hidden';
      const idAddress = e.currentTarget.getAttribute('data-deleted');
      btnConfirmDeleteAddress.parentNode.setAttribute('href',`/adresses/delete/${idAddress}`)

   })
   btnCancelDeleteAddress.addEventListener('click', ()=>{
      btnConfirmDeleteAddress.parentNode.removeAttribute('href')
      document.body.style.overflow = '';
      overlayConfirmDeleteAddress.style.display="none";
   })
});
