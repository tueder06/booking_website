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
  "Barcelona", "Madrid", "Sevilla", "Valencia",
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
        link: "pages/discover.html?search=Paris"
    },
    {
        image: "images/tokyo.jpg",
        title: "Tokyo",
        country: "Japan",
        text: "Where ancient tradition meets tomorrow.",
        link: "pages/discover.html?search=Tokyo"
    },
    {
        image: "images/barcelona.webp",
        title: "Barcelona",
        country: "Spain",
        text: "Vibrant culture and sun-kissed shores.",
        link: "pages/discover.html?search=Barcelona"
    },
    {
        image: "images/newyork.jpg",
        title: "New York",
        country: "USA",
        text: "Endless energy and world-class sights.",
        link: "pages/discover.html?search=New York"
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

export const discoverLocations = [
    {
        id: "loc_evergreen",
        name: "Luxury Evergreen Towers by Glam Apartments",
        image: "https://cf.bstatic.com/xdata/images/hotel/max1024x768/677667170.jpg?k=4a7a26129fe4109a64b4fba9bf75f13e11bff441edd8995184e1eed687804669&o=",
        rating: "Excellent 9.2",
        city: "Iași, Romania",
        distance: "4,6km from City Center",
        roomType: "Apartment with 1 bedroom",
        price: 240
    },
    {
        id: "loc_seaside",
        name: "Seaside Serenity Apartments",
        image: "https://cf.bstatic.com/xdata/images/hotel/max1024x768/762792738.jpg?k=10d7d1a3614016a95902cb8824797cd04e3f8c78136a0ba56a4b23f8ddd4290e&o=",
        rating: "Very Good 8.7",
        city: "Constanța, Romania",
        distance: "0.8km from Beach",
        roomType: "Apartment with 2 bedrooms",
        price: 180
    },
    {
        id: "loc_mountain",
        name: "Mountainview Retreat Lodge",
        image: "https://cf.bstatic.com/xdata/images/hotel/max1024x768/477203913.jpg?k=70150cc6d1e056b8e5795b7d1ead266542be64731b4cda55b3651c7641104fb6&o=",
        rating: "Excellent 9.1",
        city: "Șirnea, Romania",
        distance: "31.6km from Brașov Center",
        roomType: "Chalet with 3 bedrooms",
        price: 320
    },
    {
        id: "loc_central",
        name: "Central City Suites",
        image: "https://cf.bstatic.com/xdata/images/hotel/max1024x768/580894043.jpg?k=0a80931ff19822dade74110385d01ea4e81201699682ec7ce593ed92fcb1aa19&o=",
        rating: "Very Good 8.5",
        city: "Bucharest, Romania",
        distance: "0.5km from Old Town",
        roomType: "Studio apartment",
        price: 210
    }
];