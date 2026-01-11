document.addEventListener("DOMContentLoaded", () => {
  const roleSelect = document.getElementById("role");
  const extraFields = document.getElementById("extraFields");

  roleSelect.addEventListener("change", () => {
    const role = roleSelect.value;
    extraFields.innerHTML = "";

    if (role === "Student") {
      extraFields.innerHTML = `
        <label>اسم الجامعة:</label>
        <input type="text" name="university_name" required>

        <label>التخصص:</label>
        <input type="text" name="major" required>
      `;
    } else if (role === "CompanySupervisor") {
      extraFields.innerHTML = `
        <label>اسم الشركة:</label>
        <input type="text" name="company_name" required>

        <label>الوظيفة:</label>
        <input type="text" name="position" required>
      `;
    } else if (role === "UniversitySupervisor") {
      extraFields.innerHTML = `
        <label>اسم الجامعة:</label>
        <input type="text" name="university_name" required>

        <label>القسم:</label>
        <input type="text" name="department" required>
      `;
    }
  });
});
