const menuButton = document.querySelector('.headerMenuAccount button');
const bodyMenuAccount = document.querySelector('.bodyMenuAccount');
const menuAccountCloseMark = document.querySelector('.menuAccountCloseMark');

menuButton.addEventListener('click', ()=>{
    bodyMenuAccount.classList.toggle('bodyMenuAccountActive');
})
menuAccountCloseMark.addEventListener('click', () => {
    bodyMenuAccount.classList.remove('bodyMenuAccountActive');
})
