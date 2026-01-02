/**
 * Use this to get the dom element from a primevue component via the PrimeVue pt:root prop
 * example:
 * const myInput = ref<HTMLInputElement>();
 * <PInputText :pt:root="myInput"/>
 */
export default function getHtmlDomElementFromPrimeVue<T extends HTMLElement>() {
  let domElement: T | undefined = undefined;

  return {
    domElement,
    getDomElement: (options: { instance: { $el: T } }) => {
      domElement = (options.instance.$el);
    },
  };
}
