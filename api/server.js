const express = require("express");
const app = express();
const bodyParser = require("body-parser");
const liteApi = require("liteapi-node-sdk");
const cors = require("cors");
const path = require("path");
require("dotenv").config();

app.use(
  cors({
    origin: "*",
  })
);

const prod_apiKey = process.env.PROD_API_KEY;
const sandbox_apiKey = process.env.SAND_API_KEY;

app.use(bodyParser.json());

// Get hotels with full details
app.get("/search-hotels", async (req, res) => {
  console.log("Search endpoint hit");
  const { checkin, checkout, adults, city, countryCode, environment } = req.query;
  const apiKey = environment == "sandbox" ? sandbox_apiKey : prod_apiKey;
  const sdk = liteApi(apiKey);

  try {
    // Get hotels list with full metadata
    const hotelsResponse = await sdk.getHotels(
      countryCode,
      city,
      0,
      10
    );
    
    const hotels = hotelsResponse.data;
    const hotelIds = hotels.map((hotel) => hotel.id);

    // Get rates for hotels
    const rates = (
      await sdk.getFullRates({
        hotelIds: hotelIds,
        occupancies: [{ adults: parseInt(adults, 10) }],
        currency: "USD",
        guestNationality: "US",
        checkin: checkin,
        checkout: checkout,
      })
    ).data;

    // Combine hotel details with rates
    rates.forEach((rate) => {
      const hotelData = hotels.find((hotel) => hotel.id === rate.hotelId);
      rate.hotel = hotelData;
    });

    res.json({ rates });
  } catch (error) {
    console.error("Error searching for hotels:", error);
    res.status(500).json({ error: "Internal server error" });
  }
});

// Get places/landmarks for a city
app.get("/search-places", async (req, res) => {
  console.log("Places endpoint hit");
  const { countryCode, cityName, environment } = req.query;
  const apiKey = environment == "sandbox" ? sandbox_apiKey : prod_apiKey;
  const sdk = liteApi(apiKey);

  try {
    const placesResponse = await sdk.getPlaces(countryCode, cityName);
    
    res.json({ places: placesResponse.data || [] });
  } catch (error) {
    console.error("Error fetching places:", error);
    res.status(500).json({ error: "Internal server error", places: [] });
  }
});

// Get hotel rates
app.get("/search-rates", async (req, res) => {
  console.log("Rate endpoint hit");
  const { checkin, checkout, adults, hotelId, environment } = req.query;
  const apiKey = environment === "sandbox" ? sandbox_apiKey : prod_apiKey;
  const sdk = liteApi(apiKey);

  try {
    // Fetch rates only for the specified hotel
    const rates = (
      await sdk.getFullRates({
        hotelIds: [hotelId],
        occupancies: [{ adults: parseInt(adults, 10) }],
        currency: "USD",
        guestNationality: "US",
        checkin: checkin,
        checkout: checkout,
      })
    ).data;

    // Fetch hotel details
    const hotelsResponse = await sdk.getHotelDetails(hotelId);
    const hotelInfo = hotelsResponse.data;

    // Prepare the response data
    const rateInfo = rates.map((hotel) =>
      hotel.roomTypes.flatMap((roomType) => {
        // Define the board types we're interested in
        const boardTypes = ["RO", "BI"];

        // Filter rates by board type and sort by refundable tag
        return boardTypes
          .map((boardType) => {
            const filteredRates = roomType.rates.filter(
              (rate) => rate.boardType === boardType
            );

            // Sort to prioritize 'RFN' over 'NRFN'
            const sortedRates = filteredRates.sort((a, b) => {
              if (
                a.cancellationPolicies.refundableTag === "RFN" &&
                b.cancellationPolicies.refundableTag !== "RFN"
              ) {
                return -1; // a before b
              } else if (
                b.cancellationPolicies.refundableTag === "RFN" &&
                a.cancellationPolicies.refundableTag !== "RFN"
              ) {
                return 1; // b before a
              }
              return 0; // no change in order
            });

            // Return the first rate meeting the criteria if it exists
            if (sortedRates.length > 0) {
              const rate = sortedRates[0];
              return {
                rateName: rate.name,
                offerId: roomType.offerId,
                board: rate.boardName,
                refundableTag: rate.cancellationPolicies.refundableTag,
                retailRate: rate.retailRate.total[0].amount,
                originalRate: rate.retailRate.suggestedSellingPrice[0].amount,
              };
            }
            return null; // or some default object if no rates meet the criteria
          })
          .filter((rate) => rate !== null); // Filter out null values if no rates meet the criteria
      })
    );

    res.json({ hotelInfo, rateInfo });
  } catch (error) {
    console.error("Error fetching rates:", error);
    res.status(500).json({ error: "No availability found" });
  }
});

// Prebook endpoint
app.post("/prebook", async (req, res) => {
  const { rateId, environment, voucherCode } = req.body;
  const apiKey = environment === "sandbox" ? sandbox_apiKey : prod_apiKey;
  const sdk = liteApi(apiKey);

  const bodyData = {
    offerId: rateId,
    usePaymentSdk: true,
  };

  // Conditionally add the voucherCode if it exists in the request body
  if (voucherCode) {
    bodyData.voucherCode = voucherCode;
  }

  try {
    // Call the SDK's prebook method and handle the response
    sdk
      .preBook(bodyData)
      .then((response) => {
        res.json({ success: response }); // Send response back to the client
      })
      .catch((err) => {
        console.error("Error:", err); // Print the error if any
        res.status(500).json({ error: "Internal Server Error" }); // Send error response
      });
  } catch (err) {
    console.error("Prebook error:", err); // Handle errors related to SDK usage
    res.status(500).json({ error: "Internal Server Error" }); // Send error response
  }
});

// Book endpoint
app.get("/book", (req, res) => {
  console.log(req.query);
  const {
    prebookId,
    guestFirstName,
    guestLastName,
    guestEmail,
    transactionId,
    environment,
  } = req.query;
  const apiKey = environment === "sandbox" ? sandbox_apiKey : prod_apiKey;
  const sdk = liteApi(apiKey);

  // Prepare the booking data
  const bodyData = {
    holder: {
      firstName: guestFirstName,
      lastName: guestLastName,
      email: guestEmail,
    },
    payment: {
      method: "TRANSACTION_ID",
      transactionId: transactionId,
    },
    prebookId: prebookId,
    guests: [
      {
        occupancyNumber: 1,
        remarks: "",
        firstName: guestFirstName,
        lastName: guestLastName,
        email: guestEmail,
      },
    ],
  };

  console.log(bodyData);
  sdk
    .book(bodyData)
    .then((data) => {
      if (!data || data.error) {
        // Validate if there's any error in the data
        throw new Error(
          "Error in booking data: " +
            (data.error ? data.error.message : "Unknown error")
        );
      }
      console.log(data);
      res.send(`
        <h1>Booking Confirmed!</h1>
        <p><strong>Booking ID:</strong> ${data.data.bookingId}</p>
        <p><strong>Supplier Name:</strong> ${data.data.supplierBookingName} (${data.data.supplier})</p>
        <p><strong>Status:</strong> ${data.data.status}</p>
        <p><strong>Check-in:</strong> ${data.data.checkin}</p>
        <p><strong>Check-out:</strong> ${data.data.checkout}</p>
        <p><strong>Hotel:</strong> ${data.data.hotel.name} (ID: ${data.data.hotel.hotelId})</p>
        <p><strong>Room Type:</strong> ${data.data.bookedRooms[0].roomType.name}</p>
        <p><strong>Rate (Total):</strong> $${data.data.bookedRooms[0].rate.retailRate.total.amount} ${data.data.bookedRooms[0].rate.retailRate.total.currency}</p>
        <p><strong>Occupancy:</strong> ${data.data.bookedRooms[0].adults} Adult(s), ${data.data.bookedRooms[0].children} Child(ren)</p>
        <p><strong>Guest Name:</strong> ${data.data.bookedRooms[0].firstName} ${data.data.bookedRooms[0].lastName}</p>
        <p><strong>Cancel By:</strong> ${
          data.data.cancellationPolicies &&
          data.data.cancellationPolicies.cancelPolicyInfos &&
          data.data.cancellationPolicies.cancelPolicyInfos[0]
            ? data.data.cancellationPolicies.cancelPolicyInfos[0].cancelTime
            : "Not specified"
        }</p>
        <p><strong>Cancellation Fee:</strong> ${
          data.data.cancellationPolicies &&
          data.data.cancellationPolicies.cancelPolicyInfos &&
          data.data.cancellationPolicies.cancelPolicyInfos[0]
            ? `$${data.data.cancellationPolicies.cancelPolicyInfos[0].amount}`
            : "Not specified"
        }</p>
        <p><strong>Remarks:</strong> ${data.data.remarks || "No additional remarks."}</p>
      `);
    })
    .catch((err) => {
      console.error("Booking error:", err);
      res.status(500).json({ error: "Booking failed" });
    });
});

const PORT = 3000;
app.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
});
