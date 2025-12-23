import { ref, onUnmounted } from 'vue'

export function useSSE(url) {
  const data = ref(null)
  const connected = ref(false)
  let source = null

  function connect() {
    source = new EventSource(url)
    source.onopen = () => (connected.value = true)
    source.onmessage = (event) => {
      data.value = JSON.parse(event.data)
    }
    source.onerror = () => {
      connected.value = false
    }
  }

  function disconnect() {
    if (source) {
      source.close()
      source = null
      connected.value = false
    }
  }

  onUnmounted(disconnect)

  return { data, connected, connect, disconnect }
}
