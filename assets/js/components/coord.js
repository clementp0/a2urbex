const authorizedCoordKey = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '.', '-']
function removeUnauthorizedCoordChar(target) {
  let newString = ''

  for (let i = 0; i < target.value.length; i++) {
    const letter = target.value.charAt(i)
    if (letter === '-' && i !== 0) continue
    if (letter === '.' && newString.includes('.')) continue
    if (!authorizedCoordKey.includes(letter)) continue
    newString += letter
  }
  target.value = newString
}

function coordInputValidity(target) {
  let n = parseFloat($(target).val())
  let min = parseFloat($(target).attr('min'))
  let max = parseFloat($(target).attr('max'))
  if (n >= min && n <= max) target.setCustomValidity('')
  else target.setCustomValidity(`The value must be between ${min} and ${max}`)
}

function coordCustomPaste(target) {
  let [lat, lon] = target.value.split(',')

  let parent = $(target).parents('#new_location')
  let latEl = parent.find('#new_location_lat')
  let lonEl = parent.find('#new_location_lon')

  latEl.val(lat)
  removeUnauthorizedCoordChar(latEl[0])
  coordInputValidity(latEl[0])
  lonEl.val(lon)
  removeUnauthorizedCoordChar(lonEl[0])
  coordInputValidity(lonEl[0])
}

$(() => {
  $('.coord-input')
    .each(function (e) {
      coordInputValidity(this)
    })
    .on('keyup', function (e) {
      removeUnauthorizedCoordChar(e.target)
      coordInputValidity(e.target)
    })
    .on('paste', function (e) {
      setTimeout(() => {
        if (
          e.target.value.includes('.') &&
          e.target.value.includes(',') &&
          e.target.value.split(',').length === 2
        ) {
          coordCustomPaste(e.target)
        } else {
          removeUnauthorizedCoordChar(e.target)
          coordInputValidity(e.target)
        }
      }, 100)
    })
})
