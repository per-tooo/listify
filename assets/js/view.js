let items = document.querySelector(".items");
let item = document.querySelector("template");
let input = document.querySelector("input");

function newIdentifier() {
  return items.childElementCount;
}

function addItemToList(event) {
  if (event.keyCode === 13) {
    let clone = item.content.cloneNode(true);
    let actions = clone.querySelector(".actions");
    clone.querySelector(".text span").textContent = input.value;

    clone.firstElementChild.setAttribute("item", newIdentifier());
    actions.setAttribute("target", newIdentifier());

    actions.addEventListener("click", () => {
      items.removeChild(
        document.querySelector(`[item="${actions.getAttribute("target")}"]`)
      );
    });

    items.appendChild(clone);
  }
}

input.addEventListener("keypress", (event) => {
  addItemToList(event);
});
