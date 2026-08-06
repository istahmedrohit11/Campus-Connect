document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.querySelector('[data-nav-toggle]');
  const nav = document.querySelector('[data-nav]');
  if (toggle && nav) {
    toggle.addEventListener('click', () => {
      const open = nav.classList.toggle('open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      toggle.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
    });
    nav.querySelectorAll('a').forEach((link) => {
      link.addEventListener('click', () => {
        nav.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
      });
    });
  }

  document.querySelectorAll('[data-confirm]').forEach((button) => {
    button.addEventListener('click', (event) => {
      if (!window.confirm(button.dataset.confirm || 'Are you sure?')) event.preventDefault();
    });
  });

  document.querySelectorAll('input[type="file"]').forEach((input) => {
    input.addEventListener('change', () => {
      input.setAttribute('title', input.files?.[0]?.name || 'Choose a file');
    });
  });

  const ratingInputs = document.querySelectorAll('.star-rating input');
  const caption = document.querySelector('[data-rating-caption]');
  const labels = {1: 'Needs improvement', 2: 'Fair', 3: 'Good', 4: 'Very good', 5: 'Excellent'};
  ratingInputs.forEach((input) => {
    input.addEventListener('change', () => {
      if (caption) caption.textContent = `${input.value} star${input.value === '1' ? '' : 's'} — ${labels[input.value]}`;
    });
    if (input.checked && caption) caption.textContent = `${input.value} star${input.value === '1' ? '' : 's'} — ${labels[input.value]}`;
  });
});
