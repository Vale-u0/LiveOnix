import { useState } from "react";
import { FixedSizeList as List } from "react-window";
import { generateUsers } from "../modules/userGenerator";

function UserList() {
  const [users, setUsers] = useState([]);

  const handleGenerate = () => {
    // Aquí simulamos sobrecarga con 50,000 usuarios
    const newUsers = generateUsers(50000);
    setUsers(newUsers);
  };

  // Renderizador de cada fila en la lista virtual
  const Row = ({ index, style }) => {
    const user = users[index];
    return (
      <div style={{ ...style, padding: "5px", borderBottom: "1px solid #ccc" }}>
        {user.name} - {user.email} - Edad: {user.age}
      </div>
    );
  };

  return (
    <div style={{ padding: "20px" }}>
      <h2>Simulación de creación masiva de usuarios</h2>
      <button onClick={handleGenerate}>Generar 50,000 usuarios</button>

      {users.length > 0 && (
        <List
          height={400}          // altura del contenedor
          itemCount={users.length} // cantidad de usuarios
          itemSize={35}         // altura de cada fila
          width={"100%"}        // ancho de la lista
        >
          {Row}
        </List>
      )}
    </div>
  );
}

export default UserList;
