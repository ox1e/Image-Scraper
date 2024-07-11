let modal = document.getElementById("myModal");
let modalImg = document.getElementById("modalImg");

// Получение всех элементов с классом "image-item"
let imageItems = document.getElementsByClassName("image-item");

// Функция для открытия модального окна с изображением
function openModal(url) {
    modal.style.display = "block";
    modalImg.src = url;
}

// Функция для закрытия модального окна
function closeModal() {
    modal.style.display = "none";
}

// Закрытие модального окна при клике на фон модального окна
modal.onclick = function(event) {
    if (event.target === modal) {
        closeModal();
    }
};

// Закрытие модального окна при клике на другое изображение
for (let i = 0; i < imageItems.length; i++) {
    imageItems[i].onclick = function(event) {
        let clickedImage = event.target.closest(".image-item").querySelector("img");
        openModal(clickedImage.src);
    };
}