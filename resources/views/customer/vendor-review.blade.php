<div class="card-body">
    <h5>Rate this Vendor</h5>
    <form id="reviewForm_{{ $vendor->id }}">
        @csrf
        <div class="mb-2">
            <label class="form-label">Your Rating:</label>
            <div class="star-rating">
                <input type="radio" name="rating" value="5" id="5_{{ $vendor->id }}"><label for="5_{{ $vendor->id }}">☆</label>
                <input type="radio" name="rating" value="4" id="4_{{ $vendor->id }}"><label for="4_{{ $vendor->id }}">☆</label>
                <input type="radio" name="rating" value="3" id="3_{{ $vendor->id }}"><label for="3_{{ $vendor->id }}">☆</label>
                <input type="radio" name="rating" value="2" id="2_{{ $vendor->id }}"><label for="2_{{ $vendor->id }}">☆</label>
                <input type="radio" name="rating" value="1" id="1_{{ $vendor->id }}"><label for="1_{{ $vendor->id }}">☆</label>
            </div>
        </div>
        <div class="mb-2">
            <label for="comment" class="form-label">Comment (optional)</label>
            <textarea name="comment" class="form-control" rows="2"></textarea>
        </div>
        <button type="button" onclick="submitReview({{ $vendor->id }})" class="btn btn-sm btn-primary">Submit Review</button>
    </form>

    <hr>
    <h6>User Reviews:</h6>
    @foreach ($vendor->reviews as $review)
    <p><strong>{{ $review->user->name }}</strong>:
        {!! str_repeat('⭐', $review->rating) !!} - {{ $review->comment }}
    </p>
    @endforeach
</div>

<script>
    function submitReview(vendorId) {
        let formData = new FormData(document.getElementById('reviewForm_' + vendorId));

        fetch("{{ url('/vendor') }}/" + vendorId + "/review", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert("Review Submitted!");
                location.reload(); // Refresh page to show new review
            })
            .catch(error => console.error("Error:", error));
    }
</script>

<style>
    .star-rating {
        direction: rtl;
        display: inline-block;
    }

    .star-rating input {
        display: none;
    }

    .star-rating label {
        font-size: 20px;
        color: gray;
        cursor: pointer;
    }

    .star-rating input:checked~label {
        color: gold;
    }
</style>
