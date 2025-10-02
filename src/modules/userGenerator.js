export function generateUsers(count) {
  const users = [];
  for (let i = 0; i < count; i++) {
    users.push({
      id: i + 1,
      name: `Usuario_${i + 1}`,
      email: `usuario${i + 1}@mail.com`,
      age: Math.floor(Math.random() * 50) + 18,
    });
  }
  return users;
}
