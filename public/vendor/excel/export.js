function exportToExcel(tableId, JudulLaporan) {
    let tableData = document.getElementById(tableId).outerHTML;
    tableData = '<strong>' + JudulLaporan + '</strong>' + tableData;
    tableData = tableData.replace(/<A[^>]*>|<\/A>/g, ""); //remove if u want links in your table
    tableData = tableData.replace(/<input[^>]*>|<\/input>/gi, ""); //remove input params


    let a = document.createElement('a');
    a.href = `data:application/vnd.ms-excel, ${encodeURIComponent(tableData)}`
    a.download = JudulLaporan + '_' + getRandomNumbers() + '.xls'
    a.click()
}

function getRandomNumbers() {
    let dateObj = new Date()
    let dateTime = `${dateObj.getHours()}${dateObj.getMinutes()}${dateObj.getSeconds()}`
    return `${dateTime}${Math.floor((Math.random().toFixed(2) * 100))}`
}