export const getOrCreate = (Class, value) => (value instanceof Class ? value : new Class(value));
