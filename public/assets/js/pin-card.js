document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.art img[data-src]').forEach((img) => {
    img.addEventListener('load', () => {
      img.classList.add('loaded');
      img.closest('.art').classList.add('loaded');
    });
    img.src = img.dataset.src;
  });
});
