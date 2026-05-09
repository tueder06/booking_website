export const locationData = {
  "Australia": ["Brisbane", "Melbourne", "Perth", "Sydney"],
  "Canada": ["Calgary", "Montreal", "Toronto", "Vancouver"],
  "France": ["Bordeaux", "Lyon", "Marseille", "Paris"],
  "Germany": ["Berlin", "Frankfurt", "Hamburg", "Munich"],
  "Italy": ["Florence", "Milan", "Napoli", "Roma", "Venice"],
  "Japan": ["Hiroshima", "Kyoto", "Osaka", "Tokyo"],
  "Romania": ["Brașov", "Bucharest", "Cluj-Napoca", "Constanța", "Iași", "Timișoara"],
  "Spain": ["Barcelona", "Madrid", "Sevilla", "Valencia"],
  "United Kingdom": ["Birmingham", "Glasgow", "London", "Manchester"],
  "United States": ["Chicago", "Houston", "Los Angeles", "New York"]
};

export const activeCities = [
  "Brașov", "Bucharest", "Cluj-Napoca", "Constanța", "Iași", "Timișoara", "Șirnea",
  "Rome", "Milan", "Venice", "Naples", "Florence",
  "Madrid", "Barcelona", "Valencia", "Seville",
  "Berlin", "Munich", "Hamburg", "Frankfurt",
  "Paris", "Lyon", "Marseille", "Bordeaux",
  "Birmingham", "Glasgow", "London", "Manchester",
  "Chicago", "Houston", "Los Angeles", "New York",
  "Vienna", "Budapest", "Athens", "Istanbul", "Tokyo"
];

export const carouselData = [
    {
        image: "images/paris.webp",
        title: "Paris",
        country: "France",
        text: "Romance, art, and iconic landmarks.",
        link: "discover.php?search=Paris"
    },
    {
        image: "images/tokyo.jpg",
        title: "Tokyo",
        country: "Japan",
        text: "Where ancient tradition meets tomorrow.",
        link: "discover.php?search=Tokyo"
    },
    {
        image: "images/barcelona.webp",
        title: "Barcelona",
        country: "Spain",
        text: "Vibrant culture and sun-kissed shores.",
        link: "discover.php?search=Barcelona"
    },
    {
        image: "images/newyork.jpg",
        title: "New York",
        country: "USA",
        text: "Endless energy and world-class sights.",
        link: "discover.php?search=New York"
    }
];

export const roomsToCompare = [
    { id: 'single', type: "Single", icon: "icon-single", name: "Single Room", guests: 1, bed: "1 Single Bed", size: 15, view: "City View", bath: "Private (Shower)", kitchen: "No", balcony: "No", price: 140 },
    { id: 'standard', type: "Standard", icon: "icon-standard", name: "Standard Double", guests: 2, bed: "1 Queen Bed", size: 20, view: "City View", bath: "Private (Shower)", kitchen: "No", balcony: "No", price: 180 },
    { id: 'deluxe', type: "Deluxe", icon: "icon-deluxe", name: "Deluxe Twin", guests: 2, bed: "2 Single Beds", size: 25, view: "Garden View", bath: "Private (Bathtub)", kitchen: "No", balcony: "Yes", price: 220 },
    { id: 'family', type: "Family", icon: "icon-family", name: "Family Suite", guests: 4, bed: "1 King, 2 Singles", size: 45, view: "Pool View", bath: "2 Private Bathrooms", kitchen: "Yes", balcony: "Yes", price: 450 },
    { id: 'studio', type: "Studio", icon: "icon-studio", name: "Executive Studio", guests: 2, bed: "1 King Bed", size: 35, view: "Sea View", bath: "Private (Jacuzzi)", kitchen: "Fully Equipped", balcony: "Large Terrace", price: 320 },
    { id: 'penthouse', type: "Penthouse", icon: "icon-penthouse", name: "Penthouse Apartment", guests: 6, bed: "3 Queen Beds", size: 120, view: "Panoramic Ocean", bath: "3 Private Bathrooms", kitchen: "Premium Kitchen", balcony: "Wrap-around", price: 1200 }
];

export const tableHeaders = [
    { key: 'type', label: 'Accom. Type' },
    { key: 'name', label: 'Room Type' },
    { key: 'guests', label: 'Max Guests' },
    { key: 'bed', label: 'Bed Type' },
    { key: 'size', label: 'Room Size (m²)' },
    { key: 'view', label: 'View' },
    { key: 'bath', label: 'Bathroom Type' },
    { key: 'kitchen', label: 'Kitchenette' },
    { key: 'balcony', label: 'Balcony' },
    { key: 'price', label: 'Price / Night (RON)' }
];