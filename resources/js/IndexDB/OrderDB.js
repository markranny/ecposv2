let db;

export async function openDB() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open('OrderDatabase', 1);

    request.onerror = (event) => {
      reject('Error opening database');
    };

    request.onsuccess = (event) => {
      db = event.target.result;
      resolve(db);
    };

    request.onupgradeneeded = (event) => {
      db = event.target.result;
      const objectStore = db.createObjectStore('orders', { keyPath: 'journalid' });
      objectStore.createIndex('description', 'description', { unique: false });
      objectStore.createIndex('createddatetime', 'createddatetime', { unique: false });
    };
  });
}

export async function addOrder(order) {
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(['orders'], 'readwrite');
    const objectStore = transaction.objectStore('orders');
    const request = objectStore.add(order);

    request.onerror = (event) => {
      reject('Error adding order');
    };

    request.onsuccess = (event) => {
      resolve(event.target.result);
    };
  });
}

export async function getAllOrders() {
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(['orders'], 'readonly');
    const objectStore = transaction.objectStore('orders');
    const request = objectStore.getAll();

    request.onerror = (event) => {
      reject('Error getting orders');
    };

    request.onsuccess = (event) => {
      resolve(event.target.result);
    };
  });
}

export async function updateOrder(order) {
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(['orders'], 'readwrite');
    const objectStore = transaction.objectStore('orders');
    const request = objectStore.put(order);

    request.onerror = (event) => {
      reject('Error updating order');
    };

    request.onsuccess = (event) => {
      resolve(event.target.result);
    };
  });
}

export async function deleteOrder(journalid) {
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(['orders'], 'readwrite');
    const objectStore = transaction.objectStore('orders');
    const request = objectStore.delete(journalid);

    request.onerror = (event) => {
      reject('Error deleting order');
    };

    request.onsuccess = (event) => {
      resolve(event.target.result);
    };
  });
}