// wa-carousel makes its slide strip a tab stop (for arrow-key paging). The quote slides
// hold no links and the prev/next buttons and dots already page, so the extra stop just
// delays reaching them. WA sets tabindex once in its template, so this survives re-renders.
const removeSlideTabStop = async (carousel) => {
  await carousel.updateComplete;
  carousel.shadowRoot?.querySelector('[part~="scroll-container"]')?.setAttribute('tabindex', '-1');
};

customElements.whenDefined('wa-carousel').then(() => {
  document.querySelectorAll('.c-quote-carousel__carousel').forEach(removeSlideTabStop);
});
