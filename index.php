<?php

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
?>

<div class="content-block">
    <h2>Выбор служебного автомобиля</h2>
    <p>Укажите дату и время поездки, чтобы посмотреть доступные автомобили.</p>

    <form id="carSearchForm" class="car-search-form" onsubmit="return false;">
        <div class="form-row">
            <label for="start_date">Начало поездки:</label><br>
            <input type="datetime-local" id="start_date" name="start_date" required>
        </div>

        <div class="form-row">
            <label for="end_date">Окончание поездки:</label><br>
            <input type="datetime-local" id="end_date" name="end_date" required>
        </div>

        <div class="form-row">
            <button type="button" id="checkCarsBtn" class="button">Показать доступные</button>
        </div>
    </form>

    <div id="carResults" class="car-results" style="margin-top: 20px;"></div>
</div>

<style>
    .content-block {
        padding: 20px;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 6px;
    }

    .car-search-form .form-row {
        margin-bottom: 15px;
    }

    .car-search-form input[type="datetime-local"] {
        width: 250px;
        padding: 6px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 14px;
    }

    .button {
        background: #f6b300;
        color: #000;
        font-weight: bold;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
    }

    .button:hover {
        background: #ffcb00;
    }

    .car-card {
        border: 1px solid #ccc;
        background: #fafafa;
        border-radius: 4px;
        padding: 10px;
        margin-bottom: 10px;
    }

    .car-card h4 {
        margin: 0 0 5px;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('checkCarsBtn');
        const resultBlock = document.getElementById('carResults');

        btn.addEventListener('click', async () => {
            const start = document.getElementById('start_date').value;
            const end = document.getElementById('end_date').value;

            if (!start || !end) {
                resultBlock.innerHTML = '<p style="color:red;">Укажите даты поездки.</p>';
                return;
            }

            resultBlock.innerHTML = '<p>Загрузка...</p>';

            try {
                const response = await fetch(`/ajax/?controller=drives&action=getDrivesInfo&start=${encodeURIComponent(start)}&end=${encodeURIComponent(end)}`);
                const data = await response.json();

                if (!data.success) {
                    resultBlock.innerHTML = '<p style="color:red;">Ошибка при получении данных.</p>';
                    return;
                }

                const cars = data.data?.cars || [];

                if (!cars.length) {
                    resultBlock.innerHTML = '<p>Нет свободных автомобилей на выбранное время.</p>';
                    return;
                }

                let html = '';
                cars.forEach(car => {
                    html += `
                        <div class="car-card">
                            <p><strong>Автомобиль:</strong> ${car.model}</p>
                            <p><strong>Категория:</strong> ${car.comfort}</p>
                            <p><strong>Водитель:</strong> ${car.driver}</p>
                        </div>
                    `;
                });

                resultBlock.innerHTML = html;

            } catch (err) {
                console.error('Ошибка запроса:', err);
                resultBlock.innerHTML = '<p style="color:red;">Не удалось связаться с сервером.</p>';
            }
        });
    });
</script>

<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
