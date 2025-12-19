const express = require("express");
const cors = require("cors");
const liteApi = require("liteapi-node-sdk");
require("dotenv").config();

const app = express();
const PORT = 3000;

app.use(cors());
app.use(express.json());

const sandbox_apiKey = process.env.SAND_API_KEY;

// Search hotels
app.get("/search-hotels", async (req, res) => {
  const { checkin, checkout, adults, city, countryCode, environment } = req.query;
  const apiKey = sandbox_apiKey;
  const sdk = liteApi(apiKey);

  try {
    const hotelsResponse = await sdk.getHotels(countryCode, city, 0, 10);
    const hotels = hotelsResponse.data;
    const hotelIds = hotels.map((h) => h.id);

    const rates = (await sdk.getFullRates({
      hotelIds: hotelIds,
      occupancies: [{ adults: parseInt(adults, 10) }],
      currency: "USD",
      guestNationality: "US",
      checkin: checkin,
      checkout: checkout,
    })).data;

    rates.forEach((rate) => {
      const hotelData = hotels.find((hotel) => hotel.id === rate.hotelId);
      rate.hotel = hotelData;
    });

    res.json({ rates });
  } catch (error) {
    console.error("Error:", error);
    res.status(500).json({ error: "Internal server error" });
  }
});

// Get places
app.get("/search-places", async (req, res) => {
  const { countryCode, cityName, environment } = req.query;
  const apiKey = sandbox_apiKey;
  const sdk = liteApi(apiKey);

  try {
    const placesResponse = await sdk.getPlaces(countryCode, cityName);
    res.json({ places: placesResponse.data || [] });
  } catch (error) {
    console.error("Error:", error);
    res.status(500).json({ error: "Internal server error", places: [] });
  }
});

app.listen(PORT, () => {
  console.log(`Server running on http://localhost:${PORT}`);
});
